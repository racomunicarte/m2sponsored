<?php
/**
 * Webkul MpAuction SalesOrderPlaceAfter Observer
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Webkul\MpAuction\Model\WinnerDataFactory
     */
    private $winnerData;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAuction\Model\Product
     */
    private $auctionProduct;

    /**
     * @param CustomerSession                          $customerSession
     * @param Webkul\MpAuction\Model\WinnerDataFactory $winnerData
     * @param Webkul\MpAuction\Model\Product           $auctionProduct
     * @param Webkul\MpAuction\Helper\Data             $helperData
     */
    public function __construct(
        CustomerSession $customerSession,
        \Webkul\MpAuction\Model\WinnerDataFactory $winnerData,
        \Webkul\MpAuction\Model\Product $auctionProduct,
        \Webkul\MpAuction\Helper\Data $helperData
    ) {
        $this->customerSession = $customerSession;
        $this->winnerData = $winnerData;
        $this->auctionProduct = $auctionProduct;
        $this->helperData = $helperData;
    }

    /**
     * after place order event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId = $this->helperData->getCurrentCustomerId();
        $order = $observer->getOrder();
        foreach ($order->getAllItems() as $item) {
            $activeAucId = $this->helperData->getActiveMpAuctionId($item->getProductId());

            $auctionWinData = $this->winnerData->create()->getCollection()
                                        ->addFieldToFilter('product_id', $item->getProductId())
                                        ->addFieldToFilter('status', 1)
                                        ->addFieldToFilter('complete', 0)
                                        ->addFieldToFilter('customer_id', $customerId)
                                        ->addFieldToFilter('auction_id', $activeAucId)->setPageSize(1);

            $auctionWinData = $this->getFirstItemFromRecord($auctionWinData);
            if ($auctionWinData) {
                $winnerBidDetail = $this->helperData->getWinnerBidDetail($auctionWinData->getAuctionId());
                if ($winnerBidDetail) {
                    //bider bid row update
                    $winnerBidDetail->setShop(1);
                    $this->saveObj($winnerBidDetail);
                    //update winner Data
                    $auctionWinData->setComplete(1);
                    $this->saveObj($auctionWinData);

                    $aucPro = $this->getAuctionProduct($auctionWinData->getAuctionId());
                    //here we set auction process completely done
                    $aucPro->setAuctionStatus(4);
                    $aucPro->setStatus(1);
                    //$aucPro->setEntityId($aucPro->getId());
                    $this->saveObj($aucPro);
                }
            }
        }
        return $this;
    }

    /**
     * getAuctionProduct
     * @param int auctionProId
     * @return \Webkul\MpAuction\Model\Product
     */
    private function getAuctionProduct($auctionProId)
    {
        return $this->auctionProduct->load($auctionProId);
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
     * saveObj
     * @param Object
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }
}
