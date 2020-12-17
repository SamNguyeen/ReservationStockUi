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

namespace Magenest\ReservationStockUi\Model\Source;

class EntityType extends AbstractSource
{
    const ORDER = 5;
    const INVOICE = 6;
    const CREDITMEMO = 7;
    const SHIPMENT = 8;

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        return [
            self::ORDER => __('Order'),
            self::INVOICE => __('Invoice'),
            self::CREDITMEMO => __('Creditmemo'),
            self::SHIPMENT => __('Shipment'),
        ];
    }
}
