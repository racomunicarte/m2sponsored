<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Magneto\Sponsored\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magneto\Sponsored\Model\Sponsored;

/**
 * Webkul Marketplace SalesOrderPlaceAfterObserver Observer Model.
 */
class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magneto\Sponsored\Model\Sponsored Sponsored
     */
    protected $_sponsored;

    /**
     * SalesOrderPlaceAfterObserver constructor.
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param Sponsored $sponsored
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        Sponsored $sponsored
    ) {
        $this->_eventManager = $eventManager;
        $this->_sponsored = $sponsored;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $debug = true;
        /** @var $orderInstance Order */
        $order = $observer->getOrder();
        $lastOrderId = $order->getIncrementId();
    }
}
