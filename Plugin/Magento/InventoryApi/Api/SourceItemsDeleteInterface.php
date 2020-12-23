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

namespace Magenest\ReservationStockUi\Plugin\Magento\InventoryApi\Api;

use Magenest\ReservationStockUi\Helper\Helper;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class SourceItemsDeleteInterface
{
    protected $inventoryLog;

    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * SourceItemsSaveInterface constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Helper $helper
     * @param \Magenest\ReservationStockUi\Api\InventoryLogManagementInterface $inventoryLog
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Api\InventoryLogManagementInterface $inventoryLog
    ) {
        $this->_request = $request;
        $this->helper = $helper;
        $this->inventoryLog = $inventoryLog;
    }

    /**
     * @param \Magento\InventoryApi\Api\SourceItemsDeleteInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\InventoryApi\Api\SourceItemsDeleteInterface $subject,
        $result,
        array $sourceItems
    ) {
        if (!$this->helper->isEnableLog()) {
            return;
        }
        try {
            $action = $this->_request->getFullActionName();
            $sourceItems = $this->updateSourceDeleteItems($sourceItems);
            $this->inventoryLog->logQtyChange($sourceItems, $action ?: __('Inventory Source Item Save'));
        } catch (\Throwable $e) {
            $this->helper->debug($e);
        }
    }

    private function updateSourceDeleteItems(array $sourceItems)
    {
        $result = [];
        foreach ($sourceItems as $sourceItem) {
            /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItem */
            $item = clone $sourceItem;
            $item->setQuantity(0);
            $result[] = $item;
        }

        return $result;
    }
}
