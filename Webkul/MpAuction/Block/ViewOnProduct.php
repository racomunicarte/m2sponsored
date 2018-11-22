<?php

/**
 * Webkul_MpAuction View On Product Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block;

use Magento\Customer\Model\Session as CustomerSession;

class ViewOnProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Block\Product\Context\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProFactory;

    /**
     * @var \Webkul\MpAuction\Model\AmountFactory
     */
    private $aucAmountFactory;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuctionFactory
     */
    private $autoAuctionFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @param Magento\Catalog\Block\Product\Context   $context
     * @param Magento\Catalog\Model\Product           $product
     * @param CustomerSession                         $customerSession
     * @param Magento\Framework\Pricing\Helper\Data   $priceHelper
     * @param Webkul\Auction\Model\ProductFactory     $auctionProFactory
     * @param Webkul\Auction\Model\AmountFactory      $aucAmountFactory
     * @param Webkul\Auction\Model\AutoAuctionFactory $autoAuctionFactory
     * @param Webkul\Auction\Helper\Data              $helperData
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product $product,
        CustomerSession $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Webkul\MpAuction\Model\ProductFactory $auctionProFactory,
        \Webkul\MpAuction\Model\AmountFactory $aucAmountFactory,
        \Webkul\MpAuction\Model\AutoAuctionFactory $autoAuctionFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        array $data = []
    ) {
        $this->coreRegistry = $context->getRegistry();
        $this->product = $product;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->auctionProFactory = $auctionProFactory;
        $this->aucAmountFactory = $aucAmountFactory;
        $this->autoAuctionFactory = $autoAuctionFactory;
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @var $productList Product list of current page
     * @return array of current category product in auction and buy it now
     */
    public function getAuctionDetail()
    {
        $auctionConfig = $this->getAuctionConfiguration();
        $auctionData = false;
        if ($auctionConfig['enable']) {
            $curPro = $this->coreRegistry->registry('current_product');

            if ($curPro) {
                $currentProId = $curPro->getEntityId();
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
                $aucDataObj = $this->auctionProFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$currentProId])
                                        ->addFieldToFilter('expired', ['eq'=>0])->setPageSize(1)->getFirstItem();
                    $auctionData = $aucDataObj->getData();
                if (isset($auctionData['entity_id'])) {
                    
                    $aucEntityId = $auctionData['entity_id'];
                    $aucAmtData = $this->aucAmountFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq'=>$currentProId])
                                        ->addFieldToFilter('auction_id', ['eq'=>$aucEntityId])
                                        ->addFieldToFilter('winning_status', ['eq'=>1])
                                        ->addFieldToFilter('shop', ['neq'=>0])->setPageSize(1)->getFirstItem();

                    if ($aucAmtData->getEntityId()) {
                        $aucAmtData = $this->autoAuctionFactory->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq'=> $aucEntityId])
                                            ->addFieldToFilter('flag', ['eq'=>1])
                                            ->addFieldToFilter('shop', ['neq'=>0])->setPageSize(1)->getFirstItem();
                    }
                    $currentAuctionPriceData = $this->aucAmountFactory->create()->getCollection()
                                            ->addFieldToFilter('product_id', ['eq' => $currentProId])
                                            ->addFieldToFilter('auction_id', ['eq'=> $auctionData['entity_id']])
                                            ->setOrder('auction_amount', 'DESC')
                                            ->getFirstItem();
                    $auctionData['min_amount'] = $auctionData['starting_price'];
                    if ($currentAuctionPriceData->getAuctionAmount()) {
                        $auctionData['current_auction_amount'] = $currentAuctionPriceData->getAuctionAmount();
                        $auctionData['min_amount'] = $auctionData['current_auction_amount'];
                    } else {
                        $auctionData['current_auction_amount'] = 0.00;
                    }

                    if ($auctionData['increment_opt'] && $auctionConfig['increment_auc_enable']) {
                        $incVal = $this->helperData->getIncrementPriceAsRange($aucDataObj);
                        $minAmt = $auctionData['min_amount'];
                        $auctionData['min_amount'] = $incVal ? $minAmt + $incVal : $minAmt;
                    }

                    $today = $this->_localeDate->date()->format('m/d/y H:i:s');

                    $stopDate = date_create($auctionData['stop_auction_time']);
                    $startDate = date_create($auctionData['start_auction_time']);
                    $auctionData['stop_auction_time'] = date_format($stopDate, 'Y-m-d H:i:s');
                    $auctionData['start_auction_time'] = date_format($startDate, 'Y-m-d H:i:s');

                    $auctionData['current_time_stamp'] = strtotime($today);
                    $auctionData['start_auction_time_stamp'] = strtotime($auctionData['start_auction_time']);
                    $auctionData['stop_auction_time_stamp'] = strtotime($auctionData['stop_auction_time']);

                    $auctionData['new_auction_start'] = $aucAmtData->getEntityId() ? true : false;
                    $auctionData['auction_title'] = __('Bid on ').$curPro->getName();

                    $auctionData['pro_url'] = $this->getUrl().$curPro->getUrlKey().'.html';
                    $auctionData['pro_name'] = $curPro->getName();
                    $auctionData['pro_buy_it_now'] = in_array(1, $auctionOpt) !== false ? 1:0;
                    if (!$auctionConfig['reserve_enable'] && $currentAuctionPriceData->getEntityId()) {
                        $auctionData['pro_buy_it_now'] = 0;
                    } elseif ($auctionConfig['reserve_enable'] && $currentAuctionPriceData->getEntityId() && $auctionData['reserve_price'] <= $currentAuctionPriceData->getAuctionAmount()) {
                        $auctionData['pro_buy_it_now'] = 0;
                    }
                    if ($auctionData['min_amount'] < $auctionData['starting_price']) {
                        $auctionData['min_amount'] = $auctionData['starting_price'];
                    }
                } else {
                    $auctionData = false;
                }
            } elseif (in_array(1, $auctionOpt)) {
                $auctionData['pro_buy_it_now'] = in_array(1, $auctionOpt) !== false ? 1:0;
            }
        }
        // $this->customerSession->setAuctionData($auctionData);
        return $auctionData;
    }

    /**
     * @return array of Auction Configuration Settings
     */
    public function getAuctionConfiguration()
    {
        return $this->helperData->getAuctionConfiguration();
    }

    /**
     * @return string url of auction form
     */

    public function getAuctionFormAction()
    {
        $curPro = $this->coreRegistry->registry('current_product');
        if ($curPro) {
            $currentProId = $curPro->getEntityId();
        } else {
            $auctionId = $this->getRequest()->getParam('id');
            $currentProId = $this->getAuctionProId($auctionId);
        }
        return $this->customerSession->isLoggedIn() ?
                        $this->_urlBuilder->getUrl("mpauction/account/loginbeforebid"):
                        $this->_urlBuilder->getUrl(
                            'mpauction/account/loginbeforebid/',
                            ['id'=>$currentProId]
                        );
    }

    /**
     * getAuctionDetailAftetEnd
     * @param array $auctionData
     * @param array
     */
    public function getAuctionDetailAftetEnd($auctionData)
    {
        $currentProId = $auctionData['product_id'];
        $auctionId = $auctionData['entity_id'];

        $customerId = 0;
        $shop = '';
        $price = 0;
        $winnerBidDetail = $this->helperData->getWinnerBidDetail($auctionId);
        if ($winnerBidDetail) {
            $customerId = $winnerBidDetail->getCustomerId();
            $shop = $winnerBidDetail->getShop();
            $price = $winnerBidDetail->getBidType() == 'normal' ? $winnerBidDetail->getAuctionAmount():
                                                                $winnerBidDetail->getWinningPrice();
        }

        $waittingList = $this->aucAmountFactory->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq' => $currentProId])
                                        ->addFieldToFilter('auction_id', ['eq' => $auctionId])
                                        ->addFieldToFilter('winning_status', ['neq'=>1])
                                        ->addFieldToFilter('status', ['eq'=>0]);

        $autoWaittingList = $this->autoAuctionFactory->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq'=> $auctionId])
                                            ->addFieldToFilter('flag', ['neq' => 1])
                                            ->addFieldToFilter('status', ['eq'=>0]);
        $custList=[];

        //get watting winner list from auction amount
        foreach ($waittingList as $waitAuc) {
            if ($waitAuc->getCustomerId()!= $customerId && !in_array($waitAuc->getCustomerId(), $custList)) {
                array_push($custList, $waitAuc->getCustomerId());
            }
        }
        $autoOutBidUser = [];
        //get watting winner list from auto auction
        foreach ($autoWaittingList as $autoWaitAuc) {
            if ($autoWaitAuc->getCustomerId()!= $customerId && !in_array($autoWaitAuc->getCustomerId(), $custList)) {
                array_push($custList, $autoWaitAuc->getCustomerId());
                array_push($autoOutBidUser, $autoWaitAuc->getCustomerId());
            }
        }

        $currentUserWinner = false;
        $currentUserWaitingList = false;
        if ($this->customerSession->isLoggedIn()) {
            $currentCustomerId = $this->helperData->getCurrentCustomerId();
            if ($currentCustomerId == $customerId) {
                $day = strtotime($auctionData['stop_auction_time']. ' + '.$auctionData['days'].' days');
                $difference = $day - $auctionData['current_time_stamp'];
                $currentUserWinner = ['shop' => $shop, 'price' => $price, 'time_for_buy' => $difference];
                if ($difference < 0) {
                    $this->auctionProFactory->create()
                    ->load($auctionId)
                    ->setAuctionStatus(5)
                    ->save();
                }
            }
            
            if (in_array($currentCustomerId, $custList)) {
                $outbidmsg =  __('Bidding has been done for this product.');
                if (in_array($currentCustomerId, $autoOutBidUser) && $this->getAuctionConfiguration()['enable_auto_outbid_msg']) {
                    $outbidmsg =  $this->getAuctionConfiguration()['show_auto_outbid_msg'];
                }
                $currentUserWaitingList = [
                    'auc_list_url' => $this->_urlBuilder->getUrl('auction/index/history', ['id'=>$auctionId]),
                    'auc_url_lable' => count($waittingList).__(' Bids'),
                    'msg_lable' => $outbidmsg,
                ];
            }
        }
        return ['watting_user'=> $currentUserWaitingList,'winner' => $currentUserWinner ];
    }

    /**
     * For get Bidding history link
     * @param array $auctionDetail
     * @return string url
     */
    public function getHistoryUrl($auctionData)
    {
        return $this->_urlBuilder->getUrl('mpauction/index/history', ['id'=>$auctionData['entity_id']]);
    }

    /**
     * For get Bidding record count
     * @param int $auctionId
     * @return string
     */
    public function getNumberOfBid($auctionId)
    {
        $records = $this->aucAmountFactory->create()->getCollection()
                                            ->addFieldToFilter('auction_id', ['eq' => $auctionId]);
        return count($records).__(' Bids');
    }

    /**
     * get currency in format
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * get auction product id
     * @param $auctionId int
     * @return int
     */
    public function getAuctionProId($auctionId)
    {
        return  $this->auctionProFactory->create()->load($auctionId)->getProductId();
    }

    /**
     * getProAuctionType
     * @return string type of auction
     */
    public function getProAuctionType()
    {
        $curPro = $this->coreRegistry->registry('current_product');
        $auctionType = "";
        if ($curPro) {
            $auctionOpt = explode(',', $curPro->getAuctionType());
            if ((in_array(2, $auctionOpt) && in_array(1, $auctionOpt)) || in_array(1, $auctionOpt)) {
                $auctionType = 'buy-it-now';
            } elseif (in_array(2, $auctionOpt)) {
                $auctionType = 'auction';
            }
        }
        return $auctionType;
    }
}
