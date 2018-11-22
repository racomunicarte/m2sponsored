<?php
/**
 * Webkul MpAuction CheckoutCartProductAddAfter Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Quote\Model\Quote\Item\OptionFactory as ItemOptionValue;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutCartProductAddAfter implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */

    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */

    private $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */

    private $messageManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */

    private $stockStateInterface;

    /**
     * @var \Webkul\MpAuction\Model\WinnerDataFactory
     */
    private $winnerData;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;


    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    protected $_helperData;

    /**
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param RequestInterface                          $request
     * @param ManagerInterface                          $messageManager
     * @param StockStateInterface                       $stockStateInterface
     * @param \Webkul\MpAuction\Model\WinnerDataFactory $winnerData
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        RequestInterface $request,
        ManagerInterface $messageManager,
        StockStateInterface $stockStateInterface,
        ItemOptionValue $itemOptionValue,
        \Webkul\MpAuction\Model\WinnerDataFactory $winnerData,
        \Webkul\MpAuction\Helper\Data $helperData,
        TimezoneInterface $timezoneInterface,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->stockStateInterface = $stockStateInterface;
        $this->itemOptionValue = $itemOptionValue;
        $this->winnerData = $winnerData;
        $this->helperData = $helperData;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_auctionProductFactory = $auctionProductFactory;
    }

    /**
     * Sales quote add item event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getQuoteItem();
        $product = $event->getProduct();
        if ($this->customerSession->isLoggedIn() && !empty($product)) {
            $maxqty = 0;
            $mainArray = [];
            $customerId = $this->helperData->getCurrentCustomerId();
            $productId = $product->getId();
            $requestInfo = $this->getItembuyRequestInfo($event->getItemId());
            $params = $requestInfo ? $requestInfo : $this->request->getParams();
            $cartData = $this->checkoutSession->getQuote()->getAllItems();
            foreach ($cartData as $cart) {
                if ($cart->getProduct()->getId() == $productId) {
                    $productId = $this->getProductIdIfConfig($product, $params);

                    // To Do -need to updated logic
                    $auctionId = $this->helperData->getActiveMpAuctionId($productId);
                    $bidProCol = $this->winnerData->create()->getCollection()
                                        ->addFieldToFilter('product_id', $productId)
                                        ->addFieldToFilter('complete', 0)
                                        ->addFieldToFilter('status', 1)
                                        ->addFieldToFilter('customer_id', $customerId)
                                        ->addFieldToFilter('auction_id', $auctionId);
                    
                    $biddingProductCollection = $bidProCol->setOrder('auction_id', 'ASC')->getFirstItem();
                    if ($biddingProductCollection->getEntityId()) {
                        $auctionId = $biddingProductCollection->getAuctionId();
                        $auctionPro = $this->_auctionProductFactory->create()->load($auctionId);
                        if ($auctionPro->getEntityId() && $auctionPro->getAuctionStatus() == 0) {
                            $bidWinPrice = $biddingProductCollection->getWinAmount();
                            $cart->setOriginalCustomPrice($bidWinPrice);
                            $cart->setCustomPrice($bidWinPrice);
                            $cart->getProduct()->setIsSuperMode(true);
                        }
                    }

                    if (count($bidProCol)) {
                        foreach ($bidProCol as $winner) {
                            $day = strtotime($winner->getStopAuctionTime(). ' + '.$winner->getDays().' days');
                            $difference = $day - strtotime($this->_timezoneInterface->date()->format('m/d/y H:i:s'));
                            if ($difference > 0) {
                                $maxqty+=$winner->getMaxQty();
                                $customerArray['max']=$winner->getMaxQty();
                                $customerArray['min']=$winner->getMinQty();
                                $mainArray[$winner->getCustomerId()]=$customerArray;
                            }
                        }
                    }
                    if (array_key_exists($customerId, $mainArray)) {
                        $this->checkIfCustomerIsWinner($cart, $mainArray, $customerId);
                    } else {
                        $this->checkIfNormalCustomer($cart, $productId, $maxqty, $params);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * getProductIdIfConfig
     * @param Magento\Catalog\Model\Product $product
     * @param array $params
     * @return int
     */

    private function getProductIdIfConfig($product, $params)
    {
        $productId = $product->getId();
        if ($product->getTypeId() == 'configurable') {
            if (isset($params['super_attribute'])) {
                if (count($params['super_attribute']) > 0) {
                    $pro = $product->getTypeInstance(true)
                                    ->getProductByAttributes($params['super_attribute'], $product);
                    $productId = $pro->getId();
                }
            }
        }
        return $productId;
    }

    /**
     * getItembuyRequestInfo
     * @param int $itemId
     * @return false | array
     */
    private function getItembuyRequestInfo($itemId)
    {
        $requestInfo = false;
        $requestData = $this->itemOptionValue->create()->getCollection()
                                                ->addFieldToFilter('item_id', $itemId)
                                                ->addFieldToFilter('code', 'info_buyRequest')
                                                ->setPageSize(1)->getData();
        if (isset($requestData[0]['value']) && $requestData[0]['value'] !== '') {
            $requestInfo = $requestData[0]['value'];
        }
        return $requestInfo;
    }

    /**
     * checkIfCustomerIsWinner
     * @param cartItemObject $item
     * @param array $mainArray
     * @param int $customerId
     * @return void
     */

    private function checkIfCustomerIsWinner($item, $mainArray, $customerId)
    {
        $customerMaxQty = $mainArray[$customerId]['max'];
        $customerMinQty = $mainArray[$customerId]['min'];
        $itemQty = $item->getQty();
        if ($itemQty > $customerMaxQty) {
            $this->messageManager
                        ->addNotice(__('Maximum '. $customerMaxQty .' quantities are allowed to purchase this item.'));
            $item->setQty($customerMaxQty);
            $this->saveObj($item);
        } elseif ($itemQty < $customerMinQty) {
            $this->messageManager
                    ->addNotice(__('Minimum '. $customerMinQty .' quantities are allowed to purchase this item.'));
            $item->setQty($customerMinQty);
            $this->saveObj($item);
        }
    }

    /**
     * checkIfNormalCustomer
     * @param cartItemObject $item
     * @param int $productId
     * @param array $maxqty
     * @param array $params
     * @return void
     */

    private function checkIfNormalCustomer($item, $productId, $maxqty, $params)
    {
        $stockQty = $this->stockStateInterface->getStockQty($productId);
        $availqty = $stockQty - $maxqty;
        if ($availqty > 0) {
            if ($item->getQty() > $availqty) {
                $this->messageManager
                        ->addNotice(__('You can add Only '. $availqty .' quantities to purchase this item.'));
                $item->setQty($availqty);
                $this->saveObj($item);
            }
        } elseif (isset($params['qty'])) {
            $this->messageManager->addNotice(__('You can not add this quantities for purchase.'));
            $item->setQty($params['qty']);
            $this->saveObj($item);
        }
    }

    /**
     * saveObj
     * @param Object
     * @return void
     */
    
    private function saveObj($object)
    {
        $object->save();
    }
}
