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

namespace Magenest\ReservationStockUi\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Magenest\ReservationStockUi\Helper\Helper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog;

class MassRestore extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magenest_ReservationStockUi::delete_reservation';

    /**
     * @var Filter
     */
    protected $filter;

    private $deleteReservation;

    private $collectionFactory;

    protected $helper;

    protected $logResource;

    /**
     * MassRestore constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param InventoryLog $logResource
     * @param \Magenest\ReservationStockUi\Helper\Helper $helper
     * @param \Magenest\ReservationStockUi\Model\ResourceModel\DeleteReservationStock $deleteReservation
     * @param \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog\Grid\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog $logResource,
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Model\ResourceModel\DeleteReservationStock $deleteReservation,
        \Magenest\ReservationStockUi\Model\ResourceModel\InventoryLog\Grid\CollectionFactory $collectionFactory
    ) {
        $this->logResource = $logResource;
        $this->helper = $helper;
        $this->filter = $filter;
        $this->deleteReservation = $deleteReservation;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            $ids = [];
            foreach ($collection as $log) {
                $ids[] = $log->getId();
            }
            $this->deleteReservation->restore($collection->getItems());
            $this->logResource->deleteBatch($ids);
            $this->messageManager->addSuccessMessage(__('A total of %1 reservation(s) have been restored.', $collectionSize));
        } catch (\Throwable $e) {
            $this->helper->debug($e);
            $this->messageManager->addErrorMessage(__('Something went wrong, please try again later.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
