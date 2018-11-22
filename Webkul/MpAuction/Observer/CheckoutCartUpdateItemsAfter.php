<?php
/**
 * Webkul MpAuction CheckoutCartUpdateItemsAfter Observer.
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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Webkul\MpAuction\Model\WinnerDataFactory;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutCartUpdateItemsAfter implements ObserverInterface
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
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */

    private $configurableProTypeModel;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */

    private $auctionProductFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var TimezoneInterface
     */
    private $_timezoneInterface;

    /**
     * @param CustomerSession       $customerSession
     * @param CheckoutSession       $checkoutSession
     * @param ManagerInterface      $messageManager
     * @param StockStateInterface   $stockStateInterface
     * @param Configurable          $configurableProTypeModel
     * @param WinnerDataFactory     $winnerData
     * @param AuctionProductFactory $auctionProductFactory
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        StockStateInterface $stockStateInterface,
        Configurable $configurableProTypeModel,
        WinnerDataFactory $winnerData,
        AuctionProductFactory $auctionProductFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        TimezoneInterface $timezoneInterface
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->stockStateInterface = $stockStateInterface;
        $this->configurableProTypeModel = $configurableProTypeModel;
        $this->winnerData = $winnerData;
        $this->helperData = $helperData;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * Sales quote add item event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $maxqty='';
        $mainArray = [];
        if ($this->customerSession->isLoggedIn()) {
            $cart = $this->checkoutSession->getQuote()->getAllItems();
            $info = $observer->getInfo();
            $customerid = $this->helperData->getCurrentCustomerId();
            foreach ($cart as $item) {
                $proIds[]=$item->getProductId();
            }
            foreach ($cart as $item) {
                $product = $item->getProduct();
                $productId = $this->getProductIdIfConfigurable($product);
                $bidProCol = $this->winnerData->create()->getCollection()->addFieldToFilter('product_id', $productId)
                                                            ->addFieldToFilter('status', 1)
                                                            ->addFieldToFilter('complete', 0)
                                                            ->setOrder('auction_id');
                if (count($bidProCol)) {
                    foreach ($bidProCol as $winner) {
                        $day = strtotime($winner->getStopAuctionTime(). ' + '.$winner->getDays().' days');
                        $difference = $day - strtotime($this->_timezoneInterface->date()->format('m/d/y H:i:s'));
                        if ($difference > 0) {
                            $auctionPro = $this->auctionProductFactory->create()->getCollection()
                                                ->addFieldToFilter('entity_id', $winner->getAuctionId())
                                                ->addFieldToFilter('auction_status', 0)->setPageSize(1)
                                                ->getFirstItem();
                            if ($auctionPro->getEntityId()) {
                                $maxqty+=$winner->getMaxQty();
                                $customerArray['max']=$winner->getMaxQty();
                                $customerArray['min']=$winner->getMinQty();
                                $mainArray[$winner->getCustomerId()]=$customerArray;
                            } else {
                                $productPrice = $product->getPrice();
                                $item->setOriginalCustomPrice($productPrice);
                                $item->setCustomPrice($productPrice);
                                $item->getProduct()->setIsSuperMode(true);
                            }
                        }
                    }
                }
                if (array_key_exists($customerid, $mainArray)) {
                    $this->checkIfCustomerIsWinner($item, $mainArray, $customerid);
                } else {
                    $this->checkIfNormalCustomer($item, $productId, $maxqty, $info);
                }
            }
        }
        return $this;
    }

    /**
     * getProductIdIfConfigurable
     * @param Magento\Catalog\Model\Product $product
     * @return int
     */
    private function getProductIdIfConfigurable($product)
    {
        $productId = $product->getEntityId();
        if ($product->getTypeId() == 'configurable') {
            $childPro = $this->configurableProTypeModel->getChildrenIds($product->getId());
            $childProIds = isset($childPro[0]) ? $childPro[0]:[0];

            $biddingCollection = $this->auctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['in'=>$childProIds])
                                            ->addFieldToFilter('status', 1);
            if (!empty($biddingCollection)) {
                foreach ($biddingCollection as $bidProduct) {
                    $proId=$bidProduct->getProductId();
                    if (in_array($proId, $proIds)) {
                        $productId = $bidProduct->getProductId();
                        break;
                    }
                }
            }
        }
        return $productId;
    }

    /**
     * checkIfCustomerIsWinner
     * @param cartItemObject $item
     * @param array $mainArray
     * @param int $customerId
     * @return void
     */
    private function checkIfCustomerIsWinner($item, $mainArray, $customerid)
    {
        $customerMaxQty = $mainArray[$customerid]['max'];
        $customerMinQty = $mainArray[$customerid]['min'];
        if ($item->getQty() <= $customerMaxQty && $item->getQty()>=$customerMinQty) {
            $item->setQty($item->getQty());
            $this->saveObj($item);
        } elseif ($item->getQty() > $customerMaxQty) {
            $item->setQty($customerMaxQty);
            $this->saveObj($item);
            $this->messageManager
                    ->addNotice(__('Maximum '. $customerMaxQty .' quantities are allowed to purchase this item.'));
        } elseif ($item->getQty()<$customerMinQty) {
            $item->setQty($customerMinQty);
            $this->saveObj($item);
            $this->messageManager
                    ->addNotice(__('Minimum '. $customerMinQty .' quantities are allowed to purchase this item.'));
        }
    }

    /**
     * checkIfNormalCustomer
     * @param cartItemObject $item
     * @param int $productId
     * @param array $maxqty
     * @param array $info
     * @return void
     */
    private function checkIfNormalCustomer($item, $productId, $maxqty, $info)
    {
        $stockQty = $this->stockStateInterface->getStockQty($productId);
        $availqty =  $stockQty-$maxqty;
        if ($availqty > 0) {
            if (array_key_exists('qty', $info[$item->getId()])) {
                if ($info[$item->getId()]['qty']>=$availqty) {
                    $item->setQty($availqty);
                    $this->messageManager
                            ->addNotice(__('Maximum '. $availqty .' quantities are allowed to purchase this item.'));
                } else {
                    $item->setQty($info[$item->getId()]['qty']);
                }
                $this->saveObj($item);
            }
        } else {
            $item->setQty($item->getQty()- $params['qty']);
            $this->saveObj($item);
            $this->messageManager->addNotice('you can not add this quantity for purchase.');
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
