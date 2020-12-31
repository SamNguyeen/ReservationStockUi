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

use Magenest\ReservationStockUi\Model\Source\LogType;
use Magenest\ReservationStockUi\Api\Data\InventoryLogInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;

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

    public function loadBatch($ids)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), '*');
        $select->where(InventoryLogInterface::LOG_ID . ' IN (?)', $ids);
        $select->where(InventoryLogInterface::LOG_TYPE . ' = ?', LogType::RESERVATION);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::INVENTORY_LOG_TABLE, $this->_idFieldName);
    }
    
    public function deleteBatch($ids)
    {
        $where = $this->getConnection()->quoteInto(InventoryLogInterface::LOG_ID . ' IN (?)', $ids);
        $this->getConnection()->delete($this->getMainTable(), $where);
    }
}
