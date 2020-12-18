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

class LogType extends AbstractSource
{
    const QTY = 1;
    const RESERVATION = 2;

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        return [
            self::QTY => __('Quantity'),
            self::RESERVATION => __('Reservation')
        ];
    }
}
