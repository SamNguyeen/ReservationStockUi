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

use Magenest\ReservationStockUi\Api\InventoryLogManagementInterface;

class InventoryLogManagement implements InventoryLogManagementInterface
{
    protected $helper;

    protected $logResource;

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

    public function logQtyChange()
    {
        // TODO: Implement logQtyChange() method.
    }

    public function logReservationChange()
    {
        // TODO: Implement logReservationChange() method.
    }
}
