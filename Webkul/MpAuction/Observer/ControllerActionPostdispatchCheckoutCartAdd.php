<?php
/**
 * Webkul MpAuction ControllerActionPostdispatchCheckoutCartAdd Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;

class ControllerActionPostdispatchCheckoutCartAdd implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    private $cartHelper;
    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Webkul\MpAuction\Model\ProductFactory      $cartHelper
     * @param \Webkul\MpAuction\Model\ProductFactory      $auctionProductFactory
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
        $this->auctionProductFactory = $auctionProductFactory;
    }

    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $cartItems = $this->checkoutSession->getQuote()->getAllItems();
        $idArray = [];
        $proTypeArray = ['configurable','bundle'];
        $flag = false;
        foreach ($cartItems as $item) {
            if (!in_array($item->getProduct()->getTypeId(), $proTypeArray)) {
                $auctionProduct = $this->auctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['eq' => $item->getProductId()])
                                            ->addFieldToFilter('status', ['eq' => 1])->setPageSize(1);
                $auctionProduct = $this->getFirstItemFromRecord($auctionProduct);
                //is auction avilable
                if ($auctionProduct) {
                    $cartAddedItems = $this->cartHelper->getCart()->getItems();

                    /*remove product from cart if that product
                     *is not auction product*/
                    foreach ($cartAddedItems as $cartItem) {
                        if ($item->getItemId() != $cartItem->getItemId()) {
                            $this->removeCartItem($cartItem->getItemId());
                            $flag = true;
                        }
                    }
                }
            }
        }
        if ($flag) {
            $this->messageManager->addError(__('Only Auction product can be added to cart!.'));
        }
        return $this;
    }

    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        foreach ($collection as $row) {
            return $row;
        }
        return false;
    }

    /**
     * removeCartItem
     * @param int $itemId
     * @return void
     */
    private function removeCartItem($itemId)
    {
        $this->cartHelper->getCart()->removeItem($itemId)->save();
    }
}
