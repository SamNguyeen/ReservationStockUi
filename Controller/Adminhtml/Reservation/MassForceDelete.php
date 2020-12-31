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

namespace Magenest\ReservationStockUi\Controller\Adminhtml\Reservation;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\ReservationStockUi\Helper\Helper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassForceDelete extends \Magento\Backend\App\Action implements HttpPostActionInterface
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

    /**
     * MassForceDelete constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param Helper $helper
     * @param \Magenest\ReservationStockUi\Model\ResourceModel\DeleteReservationStock $deleteReservation
     * @param \Magenest\ReservationStockUi\Model\ResourceModel\Reservation\Grid\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magenest\ReservationStockUi\Helper\Helper $helper,
        \Magenest\ReservationStockUi\Model\ResourceModel\DeleteReservationStock $deleteReservation,
        \Magenest\ReservationStockUi\Model\ResourceModel\Reservation\Grid\CollectionFactory $collectionFactory
    ) {
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
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();

            $ids = [];
            foreach ($collection as $reservation) {
                $ids[] = $reservation->getId();
            }
            $this->deleteReservation->deleteBatch($ids);
            $this->messageManager->addSuccessMessage(__('A total of %1 reservation(s) have been deleted.', $collectionSize));
        } catch (\Throwable $e) {
            $this->helper->debug($e);
            $this->messageManager->addErrorMessage(__('Something went wrong, please try again later.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
