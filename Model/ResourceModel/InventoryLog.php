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

class InventoryLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const INVENTORY_LOG_TABLE = 'inventory_reservation_log';

    protected $_idFieldName = 'log_id';

    /**
     * @param array $logItems
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveBatch(array $logItems)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $logItems);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::INVENTORY_LOG_TABLE, $this->_idFieldName);
    }
}
