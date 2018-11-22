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

use Webkul\MpAuction\Model\ResourceModel\AutoAuction\CollectionFactory;
use Webkul\MpAuction\Model\Product as AuctionProduct;
use Webkul\MpAuction\Helper\Data as AuctionHelper;

/**
 * Auction detail block
 */
class AutoBidHistory extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'mpauction/autobidhistory.phtml';

    /**
     * @var \Webkul\MpAuction\Model\ResourceModel\AutoAuction\Collection
     */
    private $autoAuctionCollection;

    /**
     * @var \Webkul\MpAuction\Model\ResourceModel\Amount\Collection
     */
    private $auctionAmtDetails;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Customer                 $customer
     * @param \Magento\Catalog\Model\Product                   $product
     * @param \Magento\Framework\Pricing\Helper\Data           $priceHelper
     * @param CollectionFactory                                $autAucColFactory
     * @param AuctionProduct                                   $auctionProduct
     * @param AuctionHelper                                    $auctionHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        CollectionFactory $autAucColFactory,
        AuctionHelper $auctionHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->autoAuctionCollection = $autAucColFactory;
        $this->customer = $customer;
        $this->auctionHelper = $auctionHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|CollectionFactory
     */
    public function getAutoBidDetails()
    {
        $data = $this->getRequest()->getParams();
        $config = $this->getAuctionConfig();
        if (!isset($data['id']) || !$config['auto_enable'] || !$config['show_auto_details']) {
            return false;
        } elseif ($this->getRequest()->getFullActionName() == 'catalog_product_view') {
            $data['id'] = (int)$this->auctionHelper->getActiveMpAuctionId($data['id']);
        }

        if (!$this->auctionAmtDetails) {
            $this->auctionAmtDetails = $this->autoAuctionCollection->create()
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
        if ($this->getAutoBidDetails()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'auto.bidding.detail.pager')
                                        ->setCollection($this->getAutoBidDetails());
            $this->setChild('pager', $pager);
            $this->getAutoBidDetails()->load();
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
        return $config['show_autobidder_name'] ? $this->getSrarredCustomerName($customerId) : '--';
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
        return $config['show_auto_bid_amount'] ? $this->priceHelper->currency($amount, true, false) : '--';
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
