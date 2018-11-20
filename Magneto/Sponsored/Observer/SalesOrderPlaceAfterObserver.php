<?php

namespace Magneto\Sponsored\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesOrderPlaceAfterObserver
 * @package Magneto\Sponsored\Observer
 */
class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_sessionManager;

    protected $_customerSession;

    /**
     * SalesOrderPlaceAfterObserver constructor.
     * @param \Magento\Framework\Event\Manager $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_sessionManager = $sessionManager;
        $this->_customerSession = $customerSession;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $tmpSession = $this->_sessionManager->getSponsoredProduct();
        $sponsoredCollection = $this->_objectManager->create('Magneto\Sponsored\Model\ResourceModel\Sponsored\Collection');
        $sponsoredCollection->addFieldToFilter(
                'product_id', $tmpSession
            )
            ->addFieldToFilter(
                'seller_id', $this->_customerSession->getId()
            );
        foreach ($sponsoredCollection as $item) {
            $sponsored = $this->_objectManager->create('Magneto\Sponsored\Model\Sponsored');
            $model = $sponsored->load($item->getEntityId());
            $model->setIsActive(1)
                ->setProcessedOrderId($order->getIncrementId())
                ->save();
        }
    }
}
