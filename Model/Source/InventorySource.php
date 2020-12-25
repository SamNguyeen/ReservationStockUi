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

class InventorySource extends AbstractSource
{
    protected $sourceCol;

    public function __construct(
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCol
    ) {
        $this->sourceCol = $sourceCol;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        $col = $this->sourceCol->create();
        $result = [];
        foreach ($col as $source) {
            $result[$source->getSourceCode()] = $source->getName();
        }

        return $result;
    }
}
