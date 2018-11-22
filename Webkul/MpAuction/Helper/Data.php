<?php

/**
 * Webkul_MpAuction data helper
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Webkul\Marketplace\Helper\Data as MpHelperData;
use Webkul\MpAuction\Model\ResourceModel\Product\CollectionFactory as AuctCollFactory;
use Webkul\MpAuction\Model\AmountFactory;
use Webkul\MpAuction\Model\AutoAuctionFactory;
use Webkul\MpAuction\Model\IncrementalPriceFactory;
use Magento\Catalog\Model\Product;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var TimezoneInterface
     */
    private $localTimeZone;

    /**
     * @var MpHelperData
     */

    private $mpHelperData;

    /**
     * @var AutoAuctionFactory
     */
    private $autoAuctionFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var AuctCollFactory
     */
    private $auctionProFactory;

    /**
     * @var IncrementalPriceFactory
     */
    private $incrementalPriceFactory;

    /**
     * @var AmountFactorye
     */
    private $aucAmountFactory;

    /**
     * @var product
     */
    private $product;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TimezoneInterface                     $timezoneInterface
     * @param CustomerSession                       $customerSession
     * @param MpHelperData                          $mpHelperData
     * @param AuctCollFactory                       $auctionProFactory
     * @param AmountFactory                         $aucAmountFactory
     * @param AutoAuctionFactory                    $autoAuctionFactory
     * @param IncrementalPriceFactory               $incPriceFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $timezoneInterface,
        CustomerSession $customerSession,
        PriceHelper $priceHelper,
        MpHelperData $mpHelperData,
        AuctCollFactory $auctionProFactory,
        AmountFactory $aucAmountFactory,
        AutoAuctionFactory $autoAuctionFactory,
        IncrementalPriceFactory $incPriceFactory,
        Product $product
    ) {
        $this->mpHelperData = $mpHelperData;
        $this->auctionProFactory = $auctionProFactory;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->localTimeZone = $timezoneInterface;
        $this->aucAmountFactory = $aucAmountFactory;
        $this->autoAuctionFactory = $autoAuctionFactory;
        $this->incrementalPriceFactory = $incPriceFactory;
        $this->product = $product;
        parent::__construct($context);
    }

     /**
      * Get Configuration Detail of Auction
      * @return array of Auction Configuration Detail
      */
   
    public function getAuctionConfiguration()
    {
        $auctionConfig=[
            'enable' => $this->scopeConfig->getValue('wk_mpauction/general_settings/enable'),
            'auction_rule' => $this->scopeConfig->getValue('wk_mpauction/general_settings/auction_rule'),
            'show_bidder' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_bidder'),
            'show_price' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_price'),
            'reserve_enable' => $this->scopeConfig->getValue('wk_mpauction/reserve_option/enable'),
            'reserve_price' => $this->scopeConfig->getValue('wk_mpauction/reserve_option/price'),
            'show_curt_auc_price' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_curt_auc_price'),
            'show_auc_detail' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_auc_detail'),
            'auto_enable' => $this->scopeConfig->getValue('wk_mpauction/auto/enable'),
            'auto_auc_limit' => $this->scopeConfig->getValue('wk_mpauction/auto/limit'),
            'show_auto_details' => $this->scopeConfig->getValue('wk_mpauction/auto/show_auto_details'),
            'auto_use_increment' => $this->scopeConfig->getValue('wk_mpauction/auto/use_increment'),
            'show_autobidder_name' => $this->scopeConfig->getValue('wk_mpauction/auto/show_autobidder_name'),
            'show_auto_bid_amount' => $this->scopeConfig->getValue('wk_mpauction/auto/show_bid_amount'),
            'show_auto_outbid_msg' => $this->scopeConfig->getValue('wk_mpauction/auto/show_auto_outbid_msg'),
            'enable_auto_outbid_msg' => $this->scopeConfig->getValue('wk_mpauction/auto/enable_auto_outbid_msg'),
            'show_winner_msg' => $this->scopeConfig->getValue('wk_mpauction/general_settings/show_winner_msg'),
            'increment_auc_enable' => $this->scopeConfig->getValue('wk_mpauction/increment_option/enable'),
            'enable_admin_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_admin_email'),
            'admin_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/admin_notify_email_template'),
            'enable_seller_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_seller_email'),
            'seller_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/seller_notify_email_template'),
            'enable_outbid_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_outbid_email'),
            'outbid_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/outbid_notify_email_template'),
            'enable_winner_notify_email'  =>  $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/enable_winner_notify_email'),
            'winner_notify_email_template' => $this->scopeConfig
                                                    ->getValue('wk_mpauction/emails/winner_notify_email_template'),
            'admin_email_address' => $this->scopeConfig->getValue('wk_mpauction/emails/admin_email_address'),
            'enable_submit_bid_email' => $this->scopeConfig->getValue('wk_mpauction/emails/enable_submit_bid_email'),
            'bidder_notify_email_template' => $this->scopeConfig
                ->getValue('wk_mpauction/emails/bidder_notify_email_template')
        ];
        return $auctionConfig;
    }

    /**
     * @param object $product
     * @return html string
     */
    public function getProductAuctionDetail($product)
    {
        $product = $this->product->load($product->getEntityId());
        $modEnable = $this->scopeConfig->getValue('wk_mpauction/general_settings/enable');
        $content = "";
        $htmlDataAttr = "";
        $proByItNow = 0;
        if ($modEnable) {
            $auctionOpt = $product->getAuctionType();
            $auctionOpt = explode(',', $auctionOpt);
            $proByItNow = in_array(1, $auctionOpt) !== false ? 1:0;
            $auctionData = $this->auctionProFactory->create()
                    ->addFieldToFilter('product_id', $product->getEntityId())
                    ->addFieldToFilter('expired', 0)
                    ->addFieldToFilter('auction_status', ['in' => [1,0]])
                    ->setPageSize(1);
            $auctionData = $this->getFirstItemFromRecord($auctionData);
            $clock = "";
            if ($auctionData) {
                $currentAuctionPriceData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $product->getEntityId()])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                    ->setOrder('auction_amount', 'DESC')
                    ->getFirstItem();
                if ($currentAuctionPriceData->getAuctionAmount()) {
                    $highestBidAmount = $currentAuctionPriceData->getAuctionAmount();
                    $highestamount = $this->priceHelper
                        ->currency($currentAuctionPriceData->getAuctionAmount(), true, false);
                } else {
                    $highestBidAmount = 0;
                    $highestamount = $this->priceHelper->currency(0.00, true, false);
                }
                $auctionData = $auctionData->getData();
                $today = $this->localTimeZone->date()->format('m/d/y H:i:s');
                $startAuctionTime = date_format(date_create($auctionData['start_auction_time']), 'Y-m-d H:i:s');
                $stopAuctionTime = date_format(date_create($auctionData['stop_auction_time']), 'Y-m-d H:i:s');
                $difference = strtotime($stopAuctionTime) - strtotime($today);
                if ($difference > 0 && strtotime($startAuctionTime) < strtotime($today)) {
                    $clock = '<p class="wk_cat_count_clock" data-stoptime="'.$auctionData['stop_auction_time']
                                .'" data-diff_timestamp ="'.$difference.'" data-highest-bid="'.$highestamount.'" data-highest-bid-amount="'.$highestBidAmount.'" data-open-bid-amount="'.$this->priceHelper->currency($auctionData['starting_price'], true, false).'"></p>';
                }

                $winnerBidDetail = $this->getWinnerBidDetail($auctionData['entity_id']);
                if ($winnerBidDetail && $this->customerSession->isLoggedIn()) {
                    $winnerCustomerId = $winnerBidDetail->getCustomerId();
                    $currentCustomerId = $this->getCurrentCustomerId();
                    if ($currentCustomerId == $winnerCustomerId) {
                        $price = $winnerBidDetail->getBidType() == 'normal' ? $winnerBidDetail->getAuctionAmount():
                                                                $winnerBidDetail->getWinningPrice();
                        $formatedPrice = $this->priceHelper->currency($price, true, false);
                        $shop = $winnerBidDetail->getShop();
                        $htmlDataAttr = 'data-winner="'.$shop.'" data-winning-amt="'.$formatedPrice.'"';
                    }
                }
                if (!$this->getAuctionConfiguration()['reserve_enable'] &&
                    $currentAuctionPriceData->getEntityId()) {
                    $proByItNow = 0;
                } elseif ($this->getAuctionConfiguration()['reserve_enable'] && $currentAuctionPriceData->getEntityId() && $auctionData['reserve_price'] <= $currentAuctionPriceData->getAuctionAmount()) {
                    $proByItNow = 0;
                }
            }
            
            /**
             * 2 : use for auction
             * 1 : use for Buy it now
             */
            $content = "";
            if (in_array(2, $auctionOpt) && $proByItNow) {
                $content = '<div class="auction buy-it-now" '.$htmlDataAttr.'>'.$clock.'</div>';
            } elseif (in_array(2, $auctionOpt)) {
                $content = '<div class="auction" '.$htmlDataAttr.'>'.$clock.'</div>';
            } elseif ($proByItNow) {
                $content = '<div class="buy-it-now"></div>';
            }
        }
        return $content;
    }

    /**
     * $auctionId
     * @param int $auctionId auction product id
     * @return AmountFactory || AutoAuctionFactory
     */
    public function getWinnerBidDetail($auctionId)
    {
        $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                                ->addFieldToFilter('auction_id', $auctionId)
                                                ->addFieldToFilter('winning_status', 1)
                                                ->addFieldToFilter('status', 0)->setPageSize(1);

        $aucAmtData = $this->getFirstItemFromRecord($aucAmtData);
        if ($aucAmtData && $aucAmtData->getEntityId()) {
            $aucAmtData->setBidType('normal');
            return $aucAmtData;
        } else {
            $aucAmtData = $this->autoAuctionFactory->create()->getCollection()
                                        ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                                        ->addFieldToFilter('flag', ['eq'=>1])
                                        ->addFieldToFilter('status', ['eq'=>0])->setPageSize(1);
            $aucAmtData = $this->getFirstItemFromRecord($aucAmtData);
            if ($aucAmtData && $aucAmtData->getEntityId()) {
                $aucAmtData->setBidType('auto');
                return $aucAmtData;
            }
        }
        return false;
    }

    /**
     * get incremental price value
     * @param Webkul\MpAuction\Model\Product $aucDetail
     * @param float $minPrice
     * @return false|float
     */
    public function getIncrementPriceAsRange($aucDetail, $minPrice = false)
    {
        $productInfo = $this->mpHelperData->getSellerProductDataByProductId($aucDetail->getProductId())
                                                ->setPageSize(1);
        $productInfoItem = $this->getFirstItemFromRecord($productInfo);
        $incPriceRang = false;
        if ($productInfo->getSize() && $productInfoItem->getSellerId() && 'null' !== $aucDetail->getIncrementPrice()) {
            $incPriceRang = $aucDetail->getIncrementPrice();
        } else {
            $incPriceRang = $this->incrementalPriceFactory->create()->getCollection()->setPageSize(1);
            $incPriceRang = $this->getFirstItemFromRecord($incPriceRang);
            $incPriceRang = $incPriceRang ? $incPriceRang->getIncval() : false;
        }
        $minAmount = $minPrice ? $minPrice : $aucDetail->getMinAmount();
        
        if ($incPriceRang) {
            $incPriceRang = json_decode($incPriceRang, true);
            if (is_array($incPriceRang)) {
                foreach ($incPriceRang as $range => $value) {
                    $range = explode('-', $range);
                    if ($minAmount >= $range[0] && $minAmount <= $range[1]) {
                        return floatval($value);
                    }
                }
            }
        }
        return false;
    }

    /**
     * get MpActive Auction Id
     * @param $productId int
     * @return int|false
     */
    public function getActiveMpAuctionId($productId)
    {
        $auctionData = $this->auctionProFactory->create()->addFieldToFilter('product_id', ['eq'=>$productId])
                                                ->addFieldToFilter('status', ['eq'=>0])
                                                ->addFieldToFilter('expired', ['eq'=>0])
                                                ->setOrder('entity_id')->setPageSize(1);
        $auctionData = $this->getFirstItemFromRecord($auctionData);

        return $auctionData ? $auctionData->getEntityId() : false;
    }

    /**
     * getFirstItemFromRecord
     * @param Collection Object
     * @return false | data
     */
    private function getFirstItemFromRecord($collection)
    {
        $row = false;
        foreach ($collection as $row) {
            $row =  $row;
        }
        return $row;
    }

    /**
     * get bid enable value
     *
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->scopeConfig->getValue('wk_mpauction/general_settings/enable');
    }

    /**
     * @var $productList Product list of current page
     * @return array of current category product in auction and buy it now
     */
    public function getAuctionDetail($currentProId = false)
    {
        $auctionConfig = $this->getAuctionConfiguration();
        $auctionData = false;
        if ($auctionConfig['enable']) {
            if ($currentProId) {
                $curPro = $this->product->load($currentProId);
            } else {
                $auctionId = $this->getRequest()->getParam('id');
                $currentProId = $this->getAuctionProId($auctionId);
                $curPro = $this->product->load($currentProId);
            }

            $auctionOpt = $curPro->getAuctionType();
            $auctionOpt = explode(',', $auctionOpt);
            /**
             * 2 : use for auction
             * 1 : use for Buy it now
             */
            if (in_array(2, $auctionOpt)) {
                $auctionDataobj = $this->auctionProFactory->create()
                                        ->addFieldToFilter('product_id', ['eq'=>$currentProId])
                                        ->addFieldToFilter('auction_status', ['in' => [0,1]])
                                        ->addFieldToFilter('status', ['eq'=>0])->getFirstItem();
                $auctionData = $auctionDataobj->getData();
                if (isset($auctionData['entity_id'])) {
                    if ($auctionData['increment_opt'] && $auctionConfig['increment_auc_enable']) {
                        $incVal = $this->getIncrementPriceAsRange($auctionDataobj, $auctionData['min_amount']);
                        $auctionData['min_amount'] = $incVal ? $auctionData['min_amount'] + $incVal
                                                                            : $auctionData['min_amount'];
                    }
                    
                    
                    $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['eq' => $currentProId])
                                            ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                                            ->addFieldToFilter('winning_status', ['eq'=>1])
                                            ->addFieldToFilter('shop', ['neq'=>0])->getFirstItem();

                    if ($aucAmtData->getEntityId()) {
                        $aucAmtData = $this->autoAuctionFactory->create()->getCollection()
                                                ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                                                ->addFieldToFilter('flag', ['eq'=>1])
                                                ->addFieldToFilter('shop', ['neq'=>0])->getFirstItem();
                    }

                    $today = $this->localTimeZone->date()->format('m/d/y H:i:s');
                    $auctionData['stop_auction_time'] = date_format(date_create($auctionData['stop_auction_time']), 'Y-m-d H:i:s');
                    $auctionData['start_auction_time'] = date_format(date_create($auctionData['start_auction_time']), 'Y-m-d H:i:s');

                    $auctionData['current_time_stamp'] = strtotime($today);
                    $auctionData['start_auction_time_stamp'] = strtotime($auctionData['start_auction_time']);
                    $auctionData['stop_auction_time_stamp'] = strtotime($auctionData['stop_auction_time']);
                    $auctionData['new_auction_start'] = $aucAmtData->getEntityId() ? true : false;
                    $auctionData['auction_title'] = __('Bid on ').$curPro->getName();
                   // $auctionData['pro_url'] = $curPro->getProductUrl();
                    $auctionData['pro_url'] = $this->_urlBuilder->getUrl().$curPro->getUrlKey().'.html';
                    $auctionData['pro_name'] = $curPro->getName();
                    $auctionData['pro_buy_it_now'] = in_array(1, $auctionOpt) !== false ? 1:0;
                    $auctionData['pro_auction'] = in_array(2, $auctionOpt) !== false ? 1:0;
                    if ($auctionData['min_amount'] < $auctionData['starting_price']) {
                        $auctionData['min_amount'] = $auctionData['starting_price'];
                    }
                } else {
                    $auctionData = false;
                }
            }
        }
        //$this->_customerSession->setAuctionData($auctionData);
        return $auctionData;
    }
    public function getNormalBidAmountDataByCustomerId($customerId, $productId, $auctionId)
    {
        return $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $productId])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->addFieldToFilter('customer_id', ['eq'=> $customerId])
                    ->setOrder('auction_amount', 'DESC')
                    ->getFirstItem();
    }
    public function getAutomaticBidAmountDataByCustomerId($customerId, $productId, $auctionId)
    {
        return $this->autoAuctionFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $productId])
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->addFieldToFilter('customer_id', ['eq'=> $customerId])
                    ->setOrder('amount', 'DESC')
                    ->getFirstItem();
    }
    public function checkByItOptionStatus($proId, $auctionId, $reserveAmount)
    {
        $auctionType = $this->product->load($proId)->getAuctionType();
        $types = explode(',', $auctionType);
        if (!in_array(1, $types)) {
            return false;
        }
        $reserveAuctionStatus = $this->getAuctionConfiguration()['reserve_enable'];
        if ($reserveAuctionStatus) {
            $auctionData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                    ->setOrder('auction_amount', 'DESC');
            foreach ($auctionData as $amount) {
                if ($amount->getAuctionAmount() >= $reserveAmount) {
                    return false;
                }
            }
        } else {
            $amountData = $this->aucAmountFactory->create()->getCollection()
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId]);
            if (count($amountData)) {
                return false;
            }
        }
        return true;
    }

    public function getCurrentCustomerId()
    {
        return $this->mpHelperData->getCustomerId();
    }
}
