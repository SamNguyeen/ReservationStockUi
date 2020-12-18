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

class SourceItemsSaveInterface
{
    protected $inventoryLog;

    protected $helper;

    /**
     * SourceItemsSaveInterface constructor.
     *
     * @param Helper $helper
     * @param \Magenest\ReservationStockUi\Api\InventoryLogManagementInterface $inventoryLog
     */
    public function __construct(
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Api\InventoryLogManagementInterface $inventoryLog
    ) {
        $this->helper = $helper;
        $this->inventoryLog = $inventoryLog;
    }

    /**
     * @param \Magento\InventoryApi\Api\SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $subject,
        $result,
        array $sourceItems
    ) {
        try {
            $this->inventoryLog->logQtyChange($sourceItems, __('Inventory Source Item Save'));
        } catch (\Throwable $e) {
            $this->helper->debug($e);
        }
    }
}
