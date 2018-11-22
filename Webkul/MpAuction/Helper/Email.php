<?php

/**
 * Webkul_MpAuction email helper
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Helper;

use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Webkul MpAuction Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Magento\Framework\Mail\Template\TransportBuilder;
     */
    private $transportBuilder;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customer;

    /**
     * @var Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelperData;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Framework\App\Helper\Context  $context,
     * @param tateInterface                          $inlineTranslation,
     * @param TransportBuilder                       $transportBuilder,
     * @param StoreManagerInterface                  $storeManager,
     * @param CustomerRepositoryInterface            $customer,
     * @param Product                                $product,
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper,
     * @param \Webkul\Marketplace\Helper\Data        $mpHelperData,
     * @param \Webkul\Auction\Helper\Data            $helperData
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customer,
        Product $product,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Webkul\Marketplace\Helper\Data $mpHelperData,
        \Webkul\MpAuction\Helper\Data $helperData,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->localeDate = $localeDate;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->product = $product;
        $this->priceHelper = $priceHelper;
        $this->mpHelperData = $mpHelperData;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
    }

    /**
     * [generateTemplate description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo,
        $emailTempId
    ) {
        try {
            $template =  $this->transportBuilder
                                ->setTemplateIdentifier($emailTempId)
                                ->setTemplateOptions(
                                    [
                                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                        'store' => $this->storeManager->getStore()->getId(),
                                    ]
                                )->setTemplateVars($emailTemplateVariables)
                                ->setFrom($senderInfo)
                                ->addTo(
                                    $receiverInfo['email'],
                                    $receiverInfo['name']
                                );
            return $this;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * send mail to Winner
     * @param int $winnerId winner customer id
     * @param int $productId product id which bid win
     * @param float $winnerPrice bid amount on which user win auction
     * @return void
     */
    public function sendWinnerMail(
        $winnerId,
        $productId,
        $winnerPrice
    ) {
        $customer = $this->customer->getById($winnerId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $availdate = strtotime("+".$auctionDetail['days']." days", $auctionDetail['stop_auction_time_stamp']);
        $senderInfo = [
                        'name' => __('Admin'),
                        'email' => $auctionConfig['admin_email_address']
                    ];
        $receiverInfo = [
            'name' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName()),
            'email' => $customer->getEmail()
        ];

        $emailTempVariables = [
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'proUrl' => $product->getProductUrl(),
            'message' => __('you have won following product in ').$this->formatPrice($winnerPrice).'. '
                             . __('Product actual cost is ').$this->formatPrice($product->getPrice()),
            'comment'=> __('Please go and buy this product.'),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'availdate' => date('F j, Y, G:i:s A ', $availdate),
            'winneramount' => $winnerPrice
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['winner_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * send mail to Bidder on submit bid
     * @param int $bidderId winner customer id
     * @param int $productId product id which bid win
     * @param float $bidamount bid amount on which user win auction
     * @return void
     */
    public function sendSubmitMailToBidder(
        $bidderId,
        $productId,
        $bidAmount
    ) {
        $customer = $this->customer->getById($bidderId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
                        'name' => __('Admin'),
                        'email' => $auctionConfig['admin_email_address']
                    ];
        $receiverInfo = [
            'name' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName()),
            'email' => $customer->getEmail()
        ];
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $emailTempVariables = [
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'ProductUrl' => $product->getProductUrl(),
            'message' => __('Congratulation! Your bid has been submitted successfully'),
            'bidamount' => $this->formatPrice($bidAmount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'enddate' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $auctionConfig['bidder_notify_email_template']
        );

        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * send mail to Bidder on submit auto bid
     * @param int $bidderId winner customer id
     * @param int $productId product id which bid win
     * @param float $bidamount bid amount on which user win auction
     * @return void
     */
    public function sendAutoSubmitMailToBidder(
        $bidderId,
        $productId,
        $bidAmount
    ) {
        $customer = $this->customer->getById($bidderId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
                        'name' => __('Admin'),
                        'email' => $auctionConfig['admin_email_address']
                    ];
        $receiverInfo = [
            'name' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName()),
            'email' => $customer->getEmail()
        ];
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $emailTempVariables = [
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'ProductUrl' => $product->getProductUrl(),
            'message' => __('Congratulation! Your auto bid has been submitted successfully'),
            'bidamount' => $this->formatPrice($bidAmount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'enddate' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        $this->generateTemplate(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $auctionConfig['bidder_notify_email_template']
        );

        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * send mail to admin
     * @param int $customerId customer id of bidder
     * @param int $productId product id on which bid apply
     * @param float $bidAmount bid amount which user bid
     * @return void
     */
    public function sendMailToAdmin($customerId, $productId, $bidAmount)
    {
        $customer = $this->customer->getById($customerId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email')
        ];
        $receiverInfo = [
            'name' => 'Admin',
            'email' => $auctionConfig['admin_email_address']
        ];
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $emailTempVariables = [
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'proUrl' => $product->getProductUrl(),
            'message' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName())
                            .__(' has bidded ').$this->formatPrice($bidAmount)
                            .__(' on this product'),
            'comment' => __('Please go and see more bidders.'),
            'amount' => $this->formatPrice($bidAmount),
            'amountlabel' => __('Bid Amount'),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['admin_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * send mail to seller
     * @param int $customerId customer id of bid
     * @param int $productId product id on which bid apply
     * @param float $bidAmount bid amount which user bid
     * @return void
     */

    public function sendMailToSeller($customerId, $productId, $bidAmount)
    {
        $customer = $this->customer->getById($customerId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email')
        ];

        //get product seller detail
        $sellerPro = $this->mpHelperData->getSellerProductDataByProductId($productId)
                                            ->setPageSize(1)->getFirstItem();
        if ($sellerPro->getSellerId()) {
            $seller = $this->customer->getById($sellerPro->getSellerId());
            
            $receiverInfo = [
                'name' => ucfirst($seller->getFirstName())." ".ucfirst($seller->getLastName()),
                'email' => $seller->getEmail()
            ];
            $auctionDetail = $this->helperData->getAuctionDetail($productId);
            $emailTempVariables = [
                'name' => $receiverInfo['name'],
                'productName' => $product->getName(),
                'proUrl' => $product->getProductUrl(),
                'message' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName())
                                .__(' has bidded ').$this->formatPrice($bidAmount)
                                .__(' on this product'),
                'comment' => __('Please go and see more bidders.'),
                'amountlabel' => __('Bid Amount'),
                'amount' => $this->formatPrice($bidAmount),
                'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
                'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
            ];
            try {
                $this->generateTemplate(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo,
                    $auctionConfig['admin_notify_email_template']
                );
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to send mail.'));
            }
        }
    }

    /**
     * send auto mail to seller
     * @param int $customerId customer id of bid
     * @param int $productId product id on which bid apply
     * @param float $bidAmount bid amount which user bid
     * @return void
     */
    public function sendAutoMailToSeller($customerId, $productId, $bidAmount)
    {
        $customer = $this->customer->getById($customerId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email')
        ];

        //get product seller detail
        $sellerPro = $this->mpHelperData->getSellerProductDataByProductId($productId)
                                            ->setPageSize(1)->getFirstItem();
        if ($sellerPro->getSellerId()) {
            $seller = $this->customer->getById($sellerPro->getSellerId());
            
            $receiverInfo = [
                'name' => ucfirst($seller->getFirstName())." ".ucfirst($seller->getLastName()),
                'email' => $seller->getEmail()
            ];
            $auctionDetail = $this->helperData->getAuctionDetail($productId);
            $emailTempVariables = [
                'name' => $receiverInfo['name'],
                'productName' => $product->getName(),
                'proUrl' => $product->getProductUrl(),
                'message' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName())
                                .__(' has bidded auto bid ').$this->formatPrice($bidAmount)
                                .__(' on this product'),
                'comment' => __('Please go and see more bidders.'),
                'amountlabel' => __('Auto Bid Amount'),
                'amount' => $this->formatPrice($bidAmount),
                'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
                'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
            ];
            try {
                $this->generateTemplate(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo,
                    $auctionConfig['admin_notify_email_template']
                );
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to send mail.'));
            }
        }
    }
    /**
     * sendAutoMailToAdmin sends mail to admin of auto bid
     * @param int $customerId customer id of bidder
     * @param int $productId product id on which bid apply
     * @param float $bidAmount bid amount which user bid
     */
    
    public function sendAutoMailToAdmin($customerId, $productId, $bidAmount)
    {
        $customer = $this->customer->getById($customerId);
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email')
        ];
      
        $receiverInfo = [
            'name' => 'Admin',
            'email' => $auctionConfig['admin_email_address']
        ];
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $emailTempVariables = [
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'proUrl' => $product->getProductUrl(),
            'message' => ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName())
                            .__(' has bidded auto bid ').$this->formatPrice($bidAmount).__(' on this product'),
            'comment' => __('Please go and see more bidders.'),
            'amountlabel' => __('Auto Bid Amount'),
            'amount' => $this->formatPrice($bidAmount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['admin_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * sendOutBidAutoBidder send mail to auto bidder whose auto bid is out
     * @param int $bidUserId of whom mail has been send
     * @param int $userId in of customer who places higher bid
     * @param string $productId stores product id
     */

    public function sendOutBidAutoBidder($bidUserId, $userId, $productId)
    {
        $bidUser = $this->customer->getById($bidUserId);
        $customer = $this->customer->getById($userId);
        $customerName = ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName());
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => 'Admin',
            'email' => $auctionConfig['admin_email_address']
        ];
      
        $receiverInfo = [
            'name' => ucfirst($bidUser->getFirstName())." ".ucfirst($bidUser->getLastName()),
            'email' => $bidUser->getEmail()
        ];

        $hike = 0;
        $higheramount = 0;
        $yourbid = 0;
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $bidUserPrice = $this->helperData
            ->getAutomaticBidAmountDataByCustomerId($bidUserId, $productId, $auctionDetail['entity_id']);
        $userPrice = $this->helperData
            ->getNormalBidAmountDataByCustomerId($userId, $productId, $auctionDetail['entity_id']);
        if ($bidUserPrice->getEntityId() && $userPrice->getEntityId()) {
            $hike = $userPrice->getAuctionAmount() - $bidUserPrice->getAmount();
            $yourbid = $bidUserPrice->getAmount();
            $higheramount = $userPrice->getAuctionAmount();
        }

        $emailTempVariables = [
            'autobid' => 1,
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'ProductUrl' => $product->getProductUrl(),
            'message' =>  __("New higher bid than your's bid on the product"),
            'comment' => __('Please bid again and get chance to win this product.'),
            'hike' => $this->formatPrice($hike),
            'higheramount' => $this->formatPrice($higheramount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'yourbid' => $this->formatPrice($yourbid),
            'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['outbid_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * sendMailToMembers use to send mails to all the members of normal bid]
     * @param [int] $bidUserId  [holds customer id which place last bid]
     * @param [int] $bidUserId   [to whom mail has been send]
     * @param [string] $productId [product id on which customer places bid]
     */
    public function sendMailToMembers($bidUserId, $userId, $productId)
    {
        $bidUser = $this->customer->getById($bidUserId);
        $customer = $this->customer->getById($userId);
        $customerName = ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName());
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => 'Admin',
            'email' => $auctionConfig['admin_email_address']
        ];
      
        $receiverInfo = [
            'name' => ucfirst($bidUser->getFirstName())." ".ucfirst($bidUser->getLastName()),
            'email' => $bidUser->getEmail()
        ];
        $hike = 0;
        $higheramount = 0;
        $yourbid = 0;
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $bidUserPrice = $this->helperData
            ->getNormalBidAmountDataByCustomerId($bidUserId, $productId, $auctionDetail['entity_id']);
        $userPrice = $this->helperData
            ->getNormalBidAmountDataByCustomerId($userId, $productId, $auctionDetail['entity_id']);
        if ($bidUserPrice->getEntityId() && $userPrice->getEntityId()) {
            $hike = $userPrice->getAuctionAmount() - $bidUserPrice->getAuctionAmount();
            $yourbid = $bidUserPrice->getAuctionAmount();
            $higheramount = $userPrice->getAuctionAmount();
        }
        $emailTempVariables = [
            'autobid' => 0,
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'productUrl' => $product->getProductUrl(),
            'message' => __("New higher bid than your's bid on the product"),
            'comment' => __('Please bid again and get chance to win this product.'),
            'hike' => $this->formatPrice($hike),
            'higheramount' => $this->formatPrice($higheramount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'yourbid' => $this->formatPrice($yourbid),
            'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['outbid_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * sendAutoMailUsers sends mail to users of auto bid
     * @param int $bidUserId holds customer id which place last bid
     * @param int $userId customer id
     * @param int $productId holds product id on which bid has been placed
     */

    public function sendAutoMailUsers($bidUserId, $userId, $productId)
    {
        $bidUser = $this->customer->getById($bidUserId);
        $customer = $this->customer->getById($userId);
        $customerName = ucfirst($customer->getFirstName())." ".ucfirst($customer->getLastName());
        $product = $this->product->load($productId);
        $auctionConfig = $this->helperData->getAuctionConfiguration();

        $senderInfo = [
            'name' => 'Admin',
            'email' => $auctionConfig['admin_email_address']
        ];
      
        $receiverInfo = [
            'name' => ucfirst($bidUser->getFirstName())." ".ucfirst($bidUser->getLastName()),
            'email' => $bidUser->getEmail()
        ];
        $hike = 0;
        $higheramount = 0;
        $yourbid = 0;
        $auctionDetail = $this->helperData->getAuctionDetail($productId);
        $bidUserPrice = $this->helperData
            ->getAutomaticBidAmountDataByCustomerId($bidUserId, $productId, $auctionDetail['entity_id']);
        $userPrice = $this->helperData
            ->getAutomaticBidAmountDataByCustomerId($userId, $productId, $auctionDetail['entity_id']);
        if ($bidUserPrice->getEntityId() && $userPrice->getEntityId()) {
            $hike = $userPrice->getAmount() - $bidUserPrice->getAmount();
            $yourbid = $bidUserPrice->getAmount();
            $higheramount = $userPrice->getAmount();
        }
        $emailTempVariables = [
            'autobid' => 1,
            'name' => $receiverInfo['name'],
            'productName' => $product->getName(),
            'productUrl' => $product->getProductUrl(),
            'message' =>  __("New higher bid than your's bid on the product"),
            'comment' => __('Please bid again and get chance to win this product.'),
            'hike' => $this->formatPrice($hike),
            'higheramount' => $this->formatPrice($higheramount),
            'date' => $this->localeDate->date()->format('F j, Y, G:i:s A '),
            'yourbid' => $this->formatPrice($yourbid),
            'bidendtime' => date('F j, Y, G:i:s A ', $auctionDetail['stop_auction_time_stamp'])
        ];
        try {
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $auctionConfig['outbid_notify_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to send mail.'));
        }
    }

    /**
     * get currency in format
     * @param $amount float
     * @return string
     *
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->currency($amount, true, false);
    }
}
