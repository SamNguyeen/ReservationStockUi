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

namespace Magenest\ReservationStockUi\Api;

interface InventoryLogManagementInterface
{
    /**
     * @param array $items
     * @param null $comment
     *
     * @return void
     */
    public function logQtyChange($items, $comment = null);

    public function logReservationChange();
}
