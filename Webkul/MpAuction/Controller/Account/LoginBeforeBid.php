<?php

/**
 * Webkul_MpAuction bid save controller for login user
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Url as UrlModel;

class LoginBeforeBid extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $dirCurrencyFactory;

    /**
     * @var UrlModel
     */
    private $urlModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timeZone;

    /**
     * @var \Webkul\MpAuction\Model\AmountFactory
     */
    private $auctionAmount;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAuction\Model\AutoAuctionFactory
     */
    private $autoAuction;

    /**
     * @param Context                                     $context
     * @param PageFactory                                 $resultPageFactory
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory    $dirCurrencyFactory
     * @param RequestInterface                            $requesMpt
     * @param UrlModel                                    $urlModel
     * @param \Webkul\MpAuction\Model\AmountFactory       $auctionAmount
     * @param \Webkul\MpAuction\Model\ProductFactory      $auctionProductFactory
     * @param \Webkul\MpAuction\Helper\Data               $helperData
     * @param \Webkul\MpAuction\Model\AutoAuctionFactory  $autoAuction
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        TimezoneInterface $timeZone,
        UrlModel $urlModel,
        \Webkul\MpAuction\Model\AmountFactory $auctionAmount,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        \Webkul\MpAuction\Helper\Email $helperEmail,
        \Webkul\MpAuction\Model\AutoAuctionFactory $autoAuction
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
        $this->timeZone = $timeZone;
        $this->urlModel = $urlModel;
        $this->auctionAmount = $auctionAmount;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->helperData = $helperData;
        $this->helperEmail = $helperEmail;
        $this->autoAuction = $autoAuction;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->urlModel->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            //set Data in session if bidder not login
            $this->customerSession->setAuctionBidData($request->getParams());
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     * @var $cuntCunyCode current Currency Code
     * @var $baseCunyCode base Currency Code
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        $data = $this->customerSession->getMessages(true);
        //get data from customerSession relared to Auction
        $data = $this->customerSession->getAuctionBidData();
        $auctionData = $data = $data ? $data: $this->getRequest()->getParams();
        $this->customerSession->unsAuctionBidData();
        
        //unset data of customerSession relared to Auction
        $this->customerSession->unsAuctionBidData();
        $this->customerSession->unsAuctionData();

        $today = $this->timeZone->date()->format('m/d/y H:i:s');
        $difference = $auctionData['stop_auction_time_stamp'] - strtotime($today);

        if ($difference > 0) {
            //auction configuration detail
            $auctionConfig = $this->helperData->getAuctionConfiguration();

            //get currency according to store
            $store = $this->storeManager->getStore();
            $currencyModel = $this->dirCurrencyFactory->create();
            $baseCunyCode = $store->getBaseCurrencyCode();
            $cuntCunyCode = $store->getCurrentCurrencyCode();

            $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
            $rates = $currencyModel->getCurrencyRates($baseCunyCode, array_values($allowedCurrencies));

            $rates[$cuntCunyCode] = isset($rates[$cuntCunyCode]) ? $rates[$cuntCunyCode] : 1;

            $data['auction_id'] = $auctionData['entity_id'];
            $data['product_id'] = $auctionData['product_id'];
            $data['pro_name'] = $auctionData['pro_name'];
            $data['bidding_amount'] = $data['bidding_amount']/$rates[$cuntCunyCode];

            if ($auctionConfig['auto_enable'] && $auctionData['auto_auction_opt']
                && array_key_exists("auto_bid_allowed", $data)) {
                $this->saveAutobiddingAmount($data);
            } else {
                $this->saveBiddingAmount($data);
            }
        } else {
            $this->messageManager->addError(__('Auction time expired...'));
        }
        if (!isset($auctionData['pro_url']) || $auctionData['pro_url']=='') {
            $auctionData['pro_url'] = $this->_url->getBaseUrl();
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($auctionData['pro_url']);
    }

    /**
     * saveBiddingAmount saves amount of normal bid placed by customer
     * @param array $data stores bid data
     * @var int $val bid amount
     * @var int $userId current logged in customer's id
     * @var object $biddingModel holds data for particular bid
     * @var $minPrice int stores minimum price of bidding
     * @var $incopt stores increament option is allowed on bidding or not
     * @var $incPrice holds increment price for product
     * @var $bidCusrRecord object id customer already placed a bid
     * @var $bidModel use to strore new bidding
     */
    
    private function saveBiddingAmount($data)
    {
        $sendUserId = "";
        $customerArray = [];
        $val = $data['bidding_amount'];
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $userId = $this->helperData->getCurrentCustomerId();
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $minPrice = $biddingModel->getMinAmount();
        $currentAuctionPriceData = $this->auctionAmount->create()->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
            ->addFieldToFilter('auction_id', ['eq'=> $data['auction_id']])
            ->setOrder('auction_amount', 'DESC')
            ->getFirstItem();
        if ($currentAuctionPriceData->getAuctionAmount()) {
            $minPrice = $currentAuctionPriceData->getAuctionAmount();
        }
        if ($biddingModel->getIncrementOpt() && $auctionConfig['increment_auc_enable']) {
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $minPrice = $incVal ? $minPrice + $incVal : $minPrice;
        }
       
        if ($minPrice + 0.009999999999 >= $val) {
            $this->messageManager->addError(__('You can not bid less than or equal to current bid price.'));
        } else {
            $bidCusrRecord = $this->auctionAmount->create()->getCollection()
                                    ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                    ->addFieldToFilter('customer_id', ['eq' => $userId])
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
            if ($bidCusrRecord->getEntityId()) {
                $bidCusrRecord->setId($bidCusrRecord->getEntityId());
                $bidCusrRecord->setAuctionAmount($data['bidding_amount']);
                $bidCusrRecord->setCreatedAt($this->timeZone->date());
                $bidCusrRecord->save();
            } else {
                $bidModel = $this->auctionAmount->create();
                $bidModel->setAuctionAmount($data['bidding_amount']);
                $bidModel->setCustomerId($userId);
                $bidModel->setProductId($data['product_id']);
                $bidModel->setAuctionId($data['auction_id']);
                $bidModel->setStatus(1);
                $bidModel->save();
            }
            $autoBidList = $this->autoAuction->create()->getCollection()
                                        ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                        ->addFieldToFilter('status', ['eq' => '1']);
            if (!empty($autoBidList) && $auctionConfig['enable_outbid_email']) {
                $autoBidArray = [0];
                foreach ($autoBidList as $autoBidData) {
                    $autoBidArray[$autoBidData['customer_id']] = $autoBidData['amount'];
                }
                arsort($autoBidArray);
                if ($val > max($autoBidArray)) {
                    $sendUserId = 0;
                    foreach ($autoBidArray as $buyr => $amnt) {
                        $sendUserId = $buyr;
                        break;
                    }
                    if ($sendUserId != 0) {
                        $this->helperEmail->sendOutBidAutoBidder($sendUserId, $userId, $data['product_id']);
                    }
                }
            }
            if ($auctionConfig['enable_submit_bid_email']) {
                $this->helperEmail->sendSubmitMailToBidder($userId, $data['product_id'], $data['bidding_amount']);
            }
            $this->sendOutBidNotifyMail($auctionConfig['enable_outbid_email'], $data, $userId, $sendUserId);
            $this->notifyToAdminAndSeller(
                $auctionConfig['enable_admin_email'],
                $auctionConfig['enable_seller_email'],
                $data,
                $userId
            );
            $biddingModel->setMinAmount($val);
            $biddingModel->save();
            $this->messageManager->addSuccess(__('Your bid amount successfully saved.'));
        }
    }

    /**
     * saveAutobiddingAmount calls to store auto bid placed by customer
     * @param array $data holda data related to bidding
     * @var $userId int holds current customer id
     * @var $biddingModel object stores bidding details
     * @var $auctionConfig['auto_use_increment'] int stores whether increment option is enable in admin panel or not
     * @var $auctionConfig['auto_auc_limit'] stores whether customer can place auto bid multiple times or not
     * @var $minPrice int stores minimum price of bidding
     * @var $incopt stores increament option is allowed on bidding or not
     * @var $incprice holds increment price for product
     * @var $pid int product id on which bid is placed
     * @var $val int bidding amount placed by customer
     * @var $autoBidRecord object checks whether there is already bid already exist for current customer or not
     * @var $autoBidModel autobid model to store auto bid
     * @var $listToSendMail use to get maximum auto bid amount
     */

    private function saveAutobiddingAmount($data)
    {
        $max=0;
        $val = $data['bidding_amount'];
        $userId = $this->helperData->getCurrentCustomerId();
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $biddingModel = $this->auctionProductFactory->create()->load($data['auction_id']);
        $changeprice = $minPrice = $biddingModel->getMinAmount();

        if ($biddingModel->getIncrementOpt() && $auctionConfig['auto_use_increment']) {
            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel);
            $changeprice = $minPrice = $incVal ? $minPrice + $incVal : $minPrice;

            $incVal = $this->helperData->getIncrementPriceAsRange($biddingModel, $minPrice);
            $minPrice = $incVal ? $minPrice + $incVal : $minPrice;
        }

        if ($minPrice + 0.009999999999 >= $val) {
            $this->messageManager->addError(__('You can not auto bid less than or equal to current bid price.'));
        } else {
            $autoBidRecord = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('customer_id', ['eq' => $userId])
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->addFieldToFilter('status', ['eq' => '1'])->setPageSize(1)->getFirstItem();
            if ($autoBidRecord->getEntityId()) {
                if (!$auctionConfig['auto_auc_limit']) {
                    $this->messageManager->addError(__('You are not allowed to auto bid again.'));
                    return;
                } else {
                    $autoBidRecord->setId($autoBidRecord->getEntityId());
                    $autoBidRecord->setAmount($data['bidding_amount']);
                    $autoBidRecord->setCreatedAt($this->timeZone->date()->format('Y-m-d H:i:s'));
                    $autoBidRecord->save();
                }
            } else {
                $autoBidModel = $this->autoAuction->create();
                $autoBidModel->setAmount($data['bidding_amount']);
                $autoBidModel->setCustomerId($userId);
                $autoBidModel->setProductId($data['product_id']);
                $autoBidModel->setAuctionId($data['auction_id']);
                $autoBidModel->setStatus(1);
                $autoBidModel->save();
            }
            if ($auctionConfig['enable_submit_bid_email']) {
                $this->helperEmail->sendAutoSubmitMailToBidder($userId, $data['product_id'], $data['bidding_amount']);
            }
            $this->sendOutAutoBidNotifyMail($auctionConfig['enable_outbid_email'], $data, $userId);
            $this->notifyToAdminAndSeller(
                $auctionConfig['enable_admin_email'],
                $auctionConfig['enable_seller_email'],
                $data,
                $userId,
                'auto'
            );

            if ($auctionConfig['enable_auto_outbid_msg'] && $max > $val) {
                $this->messageManager->addError(__($auctionConfig['show_auto_outbid_msg']));
            } else {
                $biddingModel->setMinAmount($changeprice);
                $biddingModel->save();
                $this->messageManager->addSuccess(__('Your auto bid amount successfully saved'));
            }
            return ;
        }
    }

    /**
     * @param boolen $enable
     * @param array $data
     * @param int $userId
     * @param int $sendUserId
     * @return void
     */
    private function sendOutBidNotifyMail($enable, $data, $userId, $sendUserId)
    {
        if ($enable) {
            $listForSendMail = $this->auctionAmount->create()->getCollection()
                                        ->addFieldToFilter('product_id', ['eq' => $data['product_id']])
                                        ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']]);

            /**
             * @var int $customerId previous bidder customer
             * @var int $userId current bidder user
             */
            foreach ($listForSendMail as $key) {
                $custmerId = $key->getCustomerId();
                if ($custmerId != $userId && $custmerId != $sendUserId) {
                    $this->helperEmail->sendMailToMembers($custmerId, $userId, $data['product_id']);
                }
            }
        }
    }

    /**
     * @param boolen $enable
     * @param array $data
     * @param int $userId
     * @return void
     */
    private function sendOutAutoBidNotifyMail($enable, $data, $userId)
    {
        if ($enable) {
            $maxar = [0];
            $listToSendMail = $this->autoAuction->create()->getCollection()
                                    ->addFieldToFilter('auction_id', ['eq' => $data['auction_id']])
                                    ->addFieldToFilter('status', ['eq' => '1']);
            foreach ($listToSendMail as $ky) {
                array_push($maxar, $ky->getAmount());
            }
            $max = max($maxar);
            if ($max <= $data['bidding_amount']) {
                foreach ($listToSendMail as $ky) {
                    $custmerId ='';
                    if ($ky->getAmount() <= $data['bidding_amount']) {
                        $custmerId = $ky->getCustomerId();
                        if ($userId != $custmerId) {
                            $this->helperEmail->sendAutoMailUsers($custmerId, $userId, $data['product_id']);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param boolen $adminEnable
     * @param boolen $sellerEnable
     * @param array $data
     * @param int $userId
     * @param string $type
     * @return void
     */
    private function notifyToAdminAndSeller($adminNotifyEnable, $sellerNotifyEnable, $data, $userId, $type = 'normal')
    {
        if ($adminNotifyEnable) {
            if ($type == 'normal') {
                $this->helperEmail->sendMailToAdmin($userId, $data['product_id'], $data['bidding_amount']);
            } else {
                $this->helperEmail->sendAutoMailToAdmin($userId, $data['product_id'], $data['bidding_amount']);
            }
        }

        if ($sellerNotifyEnable) {
            if ($type == 'normal') {
                $this->helperEmail->sendMailToSeller($userId, $data['product_id'], $data['bidding_amount']);
            } else {
                $this->helperEmail->sendAutoMailToSeller($userId, $data['product_id'], $data['bidding_amount']);
            }
        }
    }
}
