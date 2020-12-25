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

namespace Magenest\ReservationStockUi\Cron;

use Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog;

class CleanupInventoryLog
{
    protected $helper;

    protected $logResource;

    public function __construct(
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog $logResource
    ) {
        $this->helper = $helper;
        $this->logResource = $logResource;
    }

    public function execute()
    {
        if (!$this->helper->isEnableLog()) {
            return;
        }
        $connection = $this->logResource->getConnection();
        $logTable = $connection->getTableName(InventoryLog::INVENTORY_LOG_TABLE);
        $select = $connection->select();
        $select->from($logTable, 'log_id');
        $sinceDate = $this->helper->getClearPeriod();
        $select->where('created_at <= ?', $sinceDate);

        $deleteQuery = $connection->deleteFromSelect($select, $logTable);
        $connection->query($deleteQuery);
    }
}
