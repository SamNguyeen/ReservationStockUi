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

    protected $deleteReservation;

    /**
     * CleanupReservations constructor.
     *
     * @param DeleteReservationStock $deleteReservation
     * @param \Magenest\ReservationStockUi\Helper\Helper $helper
     * @param ResourceConnection $resource
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        \Magenest\ReservationStockUi\Model\ResourceModel\DeleteReservationStock $deleteReservation,
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        ResourceConnection $resource,
        int $groupConcatMaxLen
    ) {
        parent::__construct($resource, $groupConcatMaxLen);
        $this->resource = $resource;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
        $this->helper = $helper;
        $this->deleteReservation = $deleteReservation;
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
                $this->deleteReservation->transferToLog(explode(',', $groupedReservationIds));
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
}
