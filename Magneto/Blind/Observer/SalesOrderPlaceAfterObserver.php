<?php

namespace Magneto\Blind\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesOrderPlaceAfterObserver
 * @package Magneto\Blind\Observer
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

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
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
        $tmpSession = $this->_sessionManager->getBlindProduct();
        $collection = $this->_objectManager->create('Magneto\Blind\Model\ResourceModel\Blind\Collection');
        $collection->addFieldToFilter(
                'product_id', $tmpSession
            )
            ->addFieldToFilter(
                'seller_id', $this->_customerSession->getId()
            );
        foreach ($collection as $item) {
            $blind = $this->_objectManager->create('Magneto\Blind\Model\Blind');
            $model = $blind->load($item->getEntityId());
            $model->setIsActive(1)
                ->setProcessedOrderId($order->getIncrementId())
                ->save();
        }
    }
}
