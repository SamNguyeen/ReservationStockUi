<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_ReservationStockUi extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_ReservationStockUi
 */

namespace Magenest\ReservationStockUi\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magenest\ReservationStockUi\Model\Source\LogType;
use Magenest\ReservationStockUi\Api\Data\InventoryLogInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;

class CleanupReservations extends \Magento\InventoryReservations\Model\ResourceModel\CleanupReservations
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var int
     */
    private $groupConcatMaxLen;

    protected $helper;

    /**
     * CleanupReservations constructor.
     *
     * @param \Magenest\ReservationStockUi\Helper\Helper $helper
     * @param ResourceConnection $resource
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        ResourceConnection $resource,
        int $groupConcatMaxLen
    ) {
        parent::__construct($resource, $groupConcatMaxLen);
        $this->resource = $resource;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');

        $groupedReservationIds = implode(
            ',',
            array_unique(
                array_merge(
                    $this->getReservationIdsByField('object_id'),
                    $this->getReservationIdsByField('object_increment_id')
                )
            )
        );

        $condition = [ReservationInterface::RESERVATION_ID . ' IN (?)' => explode(',', $groupedReservationIds)];
        if ($this->helper->isEnableLog()) {
            try {
                $this->transferToLog(explode(',', $groupedReservationIds));
            } catch (\Throwable $e) {
                $this->helper->debug($e);
            }
        }
        $connection->delete($reservationTable, $condition);
    }

    /**
     * Returns reservation ids by specified field.
     *
     * @param string $field
     * @return array
     */
    private function getReservationIdsByField(string $field) : array
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $select = $connection->select()
            ->from(
                $reservationTable,
                ['GROUP_CONCAT(' . ReservationInterface::RESERVATION_ID . ')']
            )
            ->group("JSON_EXTRACT(metadata, '$.$field')", "JSON_EXTRACT(metadata, '$.object_type')")
            ->having('SUM(' . ReservationInterface::QUANTITY . ') = 0');
        $connection->query('SET group_concat_max_len = ' . $this->groupConcatMaxLen);
        return $connection->fetchCol($select);
    }

    protected function transferToLog($reservationIds)
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $select = $connection->select()->from($reservationTable, [
            'stock_id',
            'sku',
            'quantity',
            'metadata'
        ]);
        $select->where(ReservationInterface::RESERVATION_ID . ' IN (?)', $reservationIds);
        $records = $connection->fetchAll($select);
        if ($records && is_array($records)) {
            $this->saveLog($records, $connection);
        }
    }

    /**
     * @param array $records
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    private function saveLog(array $records, $connection)
    {
        $insert = [];
        foreach ($records as $record) {
            if (!isset($record[ReservationInterface::SKU]) || !isset($record[ReservationInterface::QUANTITY])) {
                continue;
            }
            $log = [
                InventoryLogInterface::LOG_TYPE => LogType::RESERVATION,
                InventoryLogInterface::STOCK_ID => isset($record[ReservationInterface::STOCK_ID]) ? $record[ReservationInterface::STOCK_ID] : null,
                InventoryLogInterface::SKU => $record[ReservationInterface::SKU],
                InventoryLogInterface::QUANTITY => $record[ReservationInterface::QUANTITY],
                InventoryLogInterface::COMMENT => isset($record[ReservationInterface::METADATA]) ? $record[ReservationInterface::METADATA] : null,
            ];
            array_push($insert, $log);
        }
        if (!empty($insert)) {
            $connection->insertOnDuplicate($connection->getTableName(InventoryLog::INVENTORY_LOG_TABLE), $insert);
        }
    }
}
