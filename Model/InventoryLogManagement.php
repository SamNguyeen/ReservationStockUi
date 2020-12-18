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

namespace Magenest\ReservationStockUi\Model;

use Magenest\ReservationStockUi\Model\Source\LogType;
use Magenest\ReservationStockUi\Api\Data\InventoryLogInterface;
use Magenest\ReservationStockUi\Api\InventoryLogManagementInterface;

class InventoryLogManagement implements InventoryLogManagementInterface
{
    const LOG_BATCH = 500;

    protected $helper;

    protected $logResource;

    private $_logItems = [];

    private $_logged = [];

    /**
     * InventoryLogManagement constructor.
     *
     * @param \Magenest\ReservationStockUi\Helper\Helper $helper
     * @param ResourceModel\InventoryLog $logResource
     */
    public function __construct(
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog $logResource
    ) {
        $this->helper = $helper;
        $this->logResource = $logResource;
    }

    public function logQtyChange($items, $comment = null)
    {
        if (!is_array($items)) {
            return;
        }
        $this->clean();
        foreach ($items as $item) {
            /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface $item */
            $prepared = $this->prepareQty($item, $comment);
            $hash = hash('sha256', $this->helper->serialize($prepared));
            if (in_array($hash, $this->_logged)) {
                continue;
            }
            $this->_logItems[] = $prepared;
            $this->_logged[] = $hash;
            if (count($this->_logItems) >= self::LOG_BATCH) {
                $this->logResource->saveBatch($this->_logItems);
                $this->clean();
            }
        }
        if (!empty($this->_logItems)) {
            $this->logResource->saveBatch($this->_logItems);
        }
    }

    public function logReservationChange()
    {
        // TODO: Implement logReservationChange() method.
    }

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface $item
     * @param $comment
     *
     * @return array
     */
    protected function prepareQty($item, $comment)
    {
        return [
            InventoryLogInterface::LOG_TYPE => LogType::QTY,
            InventoryLogInterface::STOCK_ID => null,
            InventoryLogInterface::SOURCE_CODE => $item->getSourceCode(),
            InventoryLogInterface::SKU => $item->getSku(),
            InventoryLogInterface::QUANTITY => $item->getQuantity(),
            InventoryLogInterface::COMMENT => $comment,
        ];
    }

    private function clean()
    {
        $this->_logItems = [];
    }
}
