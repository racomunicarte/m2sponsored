<?php
/**
 * Webkul_MpAuction Detail block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Account;

use Magento\Customer\Model\Session as CustomerSession;
use Webkul\MpAuction\Model\ResourceModel\Amount\CollectionFactory;
use Webkul\MpAuction\Model\Product as AuctionProduct;
use Webkul\MpAuction\Helper\Data as AuctionHelper;

/**
 * Auction detail block
 */
class AuctionBidDetail extends \Webkul\MpAuction\Block\History
{
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    private $_auctionHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Customer\Model\Customer                 $customer
     * @param \Magento\Catalog\Model\Product                   $product
     * @param \Magento\Framework\Pricing\Helper\Data           $priceHelper
     * @param CollectionFactory                                $aucAmtColFactory
     * @param AuctionProduct                                   $auctionProduct
     * @param AuctionHelper                                    $auctionHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        CustomerSession $customerSession,
        CollectionFactory $aucAmtColFactory,
        AuctionProduct $auctionProduct,
        AuctionHelper $auctionHelper,
        array $data = []
    ) {
        $this->response = $response;
        $this->messageManager = $managerInterface;
        $this->customerSession = $customerSession;
        $this->_auctionHelper = $auctionHelper;
        parent::__construct(
            $context,
            $customer,
            $product,
            $priceHelper,
            $aucAmtColFactory,
            $auctionProduct,
            $auctionHelper,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $auctionData = $this->getAuctionDetail();
        $sellerId = $this->_auctionHelper->getCurrentCustomerId();
        if ($auctionData['customer_id'] != $sellerId) {
            $redirectUrl = $this->getUrl(
                'mpauction/account/auctionlist',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
            $this->messageManager->addError(__('Invalid auction.'));
            $this->response->setRedirect($redirectUrl);
        }
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Bidding Details'));
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $config = $this->getAuctionConfig();
        $customerModel = $this->getCustomerModel();
        return $customerModel->load($customerId)->getName();
    }

    /**
     * get Formated price
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        $config = $this->getAuctionConfig();
        $priceHelper = $this->getPriceHelper();
        return $priceHelper->currency($amount, true, false);
    }
}
