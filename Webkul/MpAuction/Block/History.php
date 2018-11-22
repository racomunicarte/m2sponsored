<?php
/**
 * Webkul_MpAuction Detail block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block;

use Webkul\MpAuction\Model\ResourceModel\Amount\CollectionFactory;
use Webkul\MpAuction\Model\Product as AuctionProduct;
use Webkul\MpAuction\Helper\Data as AuctionHelper;

/**
 * Auction detail block
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'mpauction/history.phtml';

    /**
     * @var \Webkul\Auction\Model\ResourceModel\Amount\Collection
     */
    private $auctionAmtCollection;

    /**
     * @var \Webkul\Auction\Model\ResourceModel\Amount\Collection
     */
    private $auctionAmtDetails;

    /**
     * @var \Webkul\Auction\Model\Auction
     */
    private $auctionProduct;
    
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
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
        CollectionFactory $aucAmtColFactory,
        AuctionProduct $auctionProduct,
        AuctionHelper $auctionHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->auctionAmtCollection = $aucAmtColFactory;
        $this->customer = $customer;
        $this->product = $product;
        $this->auctionProduct = $auctionProduct;
        $this->auctionHelper = $auctionHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Bidding Details'));
    }

    /**
     * @return bool|Webkul\Auction\Model\ResourceModel\Amount\CollectionFactory
     */
    public function getAuctionAmtDetails()
    {
        $data = $this->getRequest()->getParams();

        if (!isset($data['id'])) {
            return ;
        } elseif ($this->getRequest()->getFullActionName() == 'catalog_product_view') {
            $data['id'] = (int)$this->auctionHelper->getActiveMpAuctionId($data['id']);
        }
        if (!$this->auctionAmtDetails) {
            $this->auctionAmtDetails = $this->auctionAmtCollection->create()
                                                    ->addFieldToFilter('auction_id', $data['id'])
                                                    ->setOrder('entity_id', 'DESC');
        }
        return $this->auctionAmtDetails;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAuctionAmtDetails()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'auction.bidding.detail.pager')
                                        ->setCollection($this->getAuctionAmtDetails());
            $this->setChild('pager', $pager);
            $this->getAuctionAmtDetails()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $config = $this->getAuctionConfig();
        return $config['show_bidder'] ? $this->getSrarredCustomerName($customerId) : '--';
    }

    /**
     * get starred Customer Name
     * @return string
     */
    public function getSrarredCustomerName($customerId)
    {
        $customer = $this->customer->load($customerId);
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();
        return substr_replace($firstName, str_repeat("*", (strlen($firstName)-1)), 1).' '
            .substr_replace($lastName, str_repeat("*", (strlen($lastName)-1)), 1);
    }

    /**
     * get Formated price
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        $config = $this->getAuctionConfig();
        return $config['show_price'] ? $this->priceHelper->currency($amount, true, false) : '--';
    }

    /**
     * get Auction Status Label
     * @param $status int
     * @return string
     */
    public function getAuctionDetail()
    {
        $aucId = $this->getRequest()->getParam('id');
        if ($this->getRequest()->getFullActionName() == 'catalog_product_view') {
            $aucId = (int)$this->auctionHelper->getActiveMpAuctionId($aucId);
        }
        $auction = $this->auctionProduct->load($aucId)->getData();
        if ($auction) {
            $auction['pro_name'] = $this->product->load($auction['product_id'])->getName();
            $auction['start_auction_time'] = $this->formatDate(
                $auction['start_auction_time'],
                \IntlDateFormatter::MEDIUM,
                true
            );
            $auction['stop_auction_time'] = $this->formatDate(
                $auction['stop_auction_time'],
                \IntlDateFormatter::MEDIUM,
                true
            );
        }
        return $auction;
    }

    /**
     * get Auction Configuratin
     * @return array
     */
    public function getAuctionConfig()
    {
        return $this->auctionHelper->getAuctionConfiguration();
    }

    /**
     * getCustomerModel
     * @return \Magento\Customer\Model\Customer
     */

    public function getCustomerModel()
    {
        return $this->customer;
    }

    /**
     * get getPriceHelper
     * @return Magento\Framework\Pricing\Helper\Data
     */

    public function getPriceHelper()
    {
        return $this->priceHelper;
    }
}
