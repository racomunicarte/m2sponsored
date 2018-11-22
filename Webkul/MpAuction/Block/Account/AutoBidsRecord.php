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

use Webkul\MpAuction\Model\ResourceModel\AutoAuction\CollectionFactory;
use Webkul\MpAuction\Helper\Data as MpAuctionHelper;

/**
 * Auto Auction detail block
 */
class AutoBidsRecord extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'mpauction/autobidding.phtml';

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Webkul\MpAuction\Model\ResourceModel\AutoAuction\CollectionFactory
     */
    private $autoAuctionCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    protected $_autoAuctionDetails;

    /**
     * @var Webkul\MpAuction\Helper\Data
     */
    private $mpAuctionHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Catalog\Model\Product                   $product
     * @param \Magento\Framework\Pricing\Helper\Data           $priceHelper
     * @param CollectionFactory                                $autAucColFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        CollectionFactory $autAucColFactory,
        MpAuctionHelper $mpAuctionHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->autoAuctionCollection = $autAucColFactory;
        $this->customerSession = $customerSession;
        $this->product = $product;
        $this->mpAuctionHelper = $mpAuctionHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Auto Bidding Details'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getAutoAuctionDetails()
    {
        if (!($customerId = $this->mpAuctionHelper->getCurrentCustomerId())) {
            return false;
        }
        if (!$this->_autoAuctionDetails) {
            $this->_autoAuctionDetails = $this->autoAuctionCollection->create()
                                                ->addFieldToSelect('*')
                                                ->addFieldToFilter('customer_id', $customerId)
                                                ->setOrder('entity_id', 'desc');
        }
        return $this->_autoAuctionDetails;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAutoAuctionDetails()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'auto.bidding.detail.pager')
                                        ->setCollection($this->getAutoAuctionDetails());
            $this->setChild('pager', $pager);
            $this->getAutoAuctionDetails()->load();
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
     * @param int $id
     * @return string
     */
    public function getDeleteUrl($id)
    {
        return $this->getUrl('auction/account/deleteautobid', ['id' => $id]);
    }

    /**
     * @param int $productId
     * @return string
     */
    public function getProductDetail($productId)
    {
        $pro = $this->product->create()->load($productId);
        return ['name'=> $pro->getName(), 'url' => $pro->getProductUrl()];
    }

    /**
     * get Formated price
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->currency($amount, true, false);
    }

    /**
     * get Winning Status Label
     * @param $winningStatus int
     * @return string
     */
    public function winningStatus($auctionData)
    {
        if ($auctionData->getStatus() == 0) {
            $status = $auctionData->getFlag() == 1 ? __("Winner") : "--";
        } else {
            $status = __("Pending");
        }
        return $status;
    }

    /**
     * get Auction Status Label
     * @param $status int
     * @return string
     */
    public function status($status)
    {
        $label = [0 =>__('Complete'), 1 =>__('Pending')];
        return isset($label[$status]) ? $label[$status] : '--';
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
