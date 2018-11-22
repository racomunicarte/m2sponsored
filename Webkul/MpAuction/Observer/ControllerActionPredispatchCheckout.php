<?php
/**
 * Webkul MpAuction ControllerActionPostdispatchCheckout Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpAuction\Model\WinnerDataFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ControllerActionPredispatchCheckout implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Webkul\MpAuction\Model\WinnerDataFactory
     */
    private $winnerData;

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
     * @var ConfigurableProTypeModel
     */
    protected $configurableProTypeModel;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    protected $_helper;

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
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        ConfigurableProTypeModel $configurableProTypeModel,
        WinnerDataFactory $winnerData,
        TimezoneInterface $timezoneInterface,
        \Webkul\MpAuction\Helper\Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
        $this->winnerData = $winnerData;
        $this->configurableProTypeModel = $configurableProTypeModel;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_helper = $helper;
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
            $product = $item->getProduct();
            $productId = $this->getProductIdIfConfigurable($product);
            $bidProCol = $this->winnerData->create()->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('complete', 0)
                ->setOrder('auction_id', 'ASC');
            if (count($bidProCol)) {
                foreach ($bidProCol as $winner) {
                    $winnerCustomerId = $winner->getCustomerId();
                    $day = strtotime($winner->getStopAuctionTime(). ' + '.$winner->getDays().' days');
                    $difference = $day - strtotime($this->_timezoneInterface->date()->format('m/d/y H:i:s'));
                    if ($difference > 0 && $this->_helper->getCurrentCustomerId() == $winnerCustomerId) {
                        $auctionPro = $this->auctionProductFactory->create()->getCollection()
                                            ->addFieldToFilter('entity_id', $winner->getAuctionId())
                                            ->addFieldToFilter('auction_status', 0)->setPageSize(1);
                        $auctionPro = $this->getFirstItemFromRecord($auctionPro);
                        if (!$auctionPro) {
                            $customOptionPrice = $this->getCustomOptionPrice($item->getProduct());
                            $productPrice = $product->getPrice() + $customOptionPrice;
                            $item->setOriginalCustomPrice($productPrice);
                            $item->setCustomPrice($productPrice);
                            $item->getProduct()->setIsSuperMode(true);
                            $this->saveObj($item);
                        } else {
                            $bidWinPrice = $winner->getWinAmount();
                            $item->setOriginalCustomPrice($bidWinPrice);
                            $item->setCustomPrice($bidWinPrice);
                            $item->getProduct()->setIsSuperMode(true);
                        }
                    }
                }
                $this->saveObj($this->checkoutSession->getQuote()->collectTotals());
            }
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
     * saveObj
     * @param int $itemId
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }
    public function getCustomOptionPrice($product)
    {
        $finalPrice = 0;
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), 0);
                }
            }
        }
        return $finalPrice;
    }
}
