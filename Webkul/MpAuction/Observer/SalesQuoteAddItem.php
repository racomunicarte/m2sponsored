<?php
/**
 * Webkul MpAuction SalesQuoteAddItem Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SalesQuoteAddItem implements ObserverInterface
{
    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */

    private $customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

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
    protected $helperData;

    /**
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\App\RequestInterface     $request
     * @param \Webkul\MpAuction\Model\ProductFactory      $auctionProductFactory
     * @param \Webkul\MpAuction\Model\WinnerDataFactory   $winnerData
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        \Webkul\MpAuction\Model\WinnerDataFactory $winnerData,
        \Webkul\MpAuction\Helper\Data $helperData,
        TimezoneInterface $timezoneInterface
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->winnerData = $winnerData;
        $this->helperData = $helperData;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * Sales quote add item event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->helperData->getCurrentCustomerId();
            $item = $observer->getQuoteItem();
            $product = $item->getProduct();
            $productId = $product->getId();
            if ($product->getTypeId()=='configurable') {
                $superAttribute = $this->request->getParam('super_attribute');
                if ($superAttribute) {
                    if (!empty($superAttribute)) {
                        //get product id according to attribute values
                        $pro = $product->getTypeInstance(true)->getProductByAttributes($superAttribute, $product);
                        $productId=$pro->getId();
                    }
                }
            }
            $biddingProductCollection = $this->winnerData->create()->getCollection()
                ->addFieldToFilter('product_id', ['eq' => $productId])
                ->addFieldToFilter('status', ['eq' => 1])
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('complete', ['eq' => 0])
                ->setOrder('auction_id')->setPageSize(1)->getFirstItem();
            if ($biddingProductCollection->getEntityId()) {
                $day = strtotime($biddingProductCollection->getStopAuctionTime(). ' + '.$biddingProductCollection->getDays().' days');
                $difference = $day - strtotime($this->_timezoneInterface->date()->format('m/d/y H:i:s'));
                if ($difference > 0) {
                    $auctionId = $biddingProductCollection->getAuctionId();
                    $auctionPro = $this->auctionProductFactory->create()->load($auctionId);
                    if ($auctionPro->getEntityId() && $auctionPro->getAuctionStatus() == 0) {
                        $bidWinPrice = $biddingProductCollection->getWinAmount();
                        $item->setOriginalCustomPrice($bidWinPrice);
                        $item->setCustomPrice($bidWinPrice);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            }
        }
        return $this;
    }
}
