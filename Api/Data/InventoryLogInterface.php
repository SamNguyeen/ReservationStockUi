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

namespace Magenest\ReservationStockUi\Api\Data;

interface InventoryLogInterface
{
    const LOG_TYPE = 'log_type';
    const STOCK_ID = 'stock_id';
    const SOURCE_CODE = 'source_code';
    const SKU = 'sku';
    const QUANTITY = 'quantity';
    const COMMENT = 'comment';
}
