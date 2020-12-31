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
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;

class DeleteReservationStock
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    protected $logResource;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    public function __construct(
        ResourceConnection $resource,
        \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog $logResource,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->logResource = $logResource;
        $this->resource = $resource;
    }

    public function deleteBatch($reservationIds)
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $condition = $connection->quoteInto(ReservationInterface::RESERVATION_ID . ' IN (?)', $reservationIds);
        $connection->delete($reservationTable, $condition);
    }

    public function transferToLog($reservationIds)
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

    public function restore($logRecords)
    {
        $reservations = [];
        foreach ($logRecords as $log) {
            if ($log->getLogType() == LogType::QTY) {
                continue;
            }
            $reservations[] = $this->reservationBuilder
                ->setSku($log->getSku())
                ->setQuantity((float)$log->getQuantity())
                ->setStockId($log->getStockId())
                ->setMetadata($log->getComment())
                ->build();
        }
        if (!empty($reservations)) {
            $this->appendReservations->execute($reservations);
        }
    }
}
