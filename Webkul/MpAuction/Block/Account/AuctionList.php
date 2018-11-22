<?php
 /**
  * Webkul_MpAuction Auction List Block.
  * @category  Webkul
  * @package   Webkul_MpAuction
  * @author    Webkul
  * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\MpAuction\Block\Account;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;
use Webkul\MpAuction\Model\ResourceModel\Product\Source\AuctionStatus;
use Webkul\MpAuction\Model\ResourceModel\Product\Source\Options as ProductOptions;
use Webkul\MpAuction\Helper\Data as AuctionHelperData;

class AuctionList extends \Webkul\Marketplace\Block\Product\Productlist
{
    /**
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProduct;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Webkul\MpAuction\Model\ResourceModel\Product\Source\AuctionStatus
     */
    private $auctionStatus;

    /**
     * @var Webkul\MpAuction\Helper\Data
     */
    private $auctionHelperData;

    /**
     * @var Webkul\MpAuction\Model\ResourceModel\Product\Source\Options
     */
    private $productOptions;

    protected $_productList;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        ObjectManagerInterface $objectManager,
        CustomerSession $customerSession,
        CollectionFactory $productCollection,
        PriceCurrencyInterface $priceCurrency,
        AuctionProduct $auctionProduct,
        ProductRepositoryInterface $productRepository,
        AuctionStatus $auctionStatus,
        AuctionHelperData $auctionHelperData,
        ProductOptions $productOptions,
        array $data = []
    ) {
        $this->auctionProduct = $auctionProduct;
        $this->productRepository = $productRepository;
        $this->auctionStatus = $auctionStatus;
        $this->auctionHelperData = $auctionHelperData;
        $this->productOptions = $productOptions;
        parent::__construct($context, $objectManager, $customerSession, $productCollection, $priceCurrency, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $collPro = parent::getAllProducts();
            $mpProArray = [0];
            foreach ($collPro as $mpProduct) {
                array_push($mpProArray, $mpProduct->getMageproductId());
            }
            $search = $this->getRequest()->getParam('s');
            $search = filter_var($search, FILTER_SANITIZE_STRING);
            $search = trim($search);
            $collection = $this->_productCollectionFactory->create()
                                ->addFieldToFilter('name', ['like' => '%'.$search.'%'])
                                ->addFieldToFilter('type_id', ['nin'=> ['grouped', 'configurable']])
                                ->addFieldToFilter('visibility', ['neq'=>1]);
            $aucProArray = [0];
            foreach ($collection as $aucPro) {
                array_push($aucProArray, $aucPro->getEntityId());
            }
            $this->_productList = $this->auctionProduct->create()->getCollection()
                ->addFieldToFilter('customer_id', $this->auctionHelperData->getCurrentCustomerId())
                ->addFieldToFilter('product_id', ['in' => $aucProArray])
                ->setOrder('entity_id', 'AESC');
        }
        return $this->_productList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'auction.pro.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
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
     * @param int $productId
     * @return url string add deal on product
     */
    public function getAddAuctionUrl($productId)
    {
        return $this->getUrl(
            'mpauction/account/addauction',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'aid'=>$productId
            ]
        );
    }

    /**
     * getDateTimeAsLocale
     * @param string $data in base Time zone
     * @return string date in current Time zone
     */
    public function getDateTimeAsLocale($data)
    {
        if ($data) {
            return date_format(date_create($data), "m/d/Y H:i:s");
        }
        return $data;
    }

    /**
     * getProductDetail
     * @param int $productId which detail want
     * @return false| Magento\Catalog\Model\Product
     */
    public function getProductDetail($productId)
    {
        if ($productId) {
            return $this->productRepository->getById($productId);
        }
        return false;
    }

    /**
     * getAuctionStatusLabel
     * @param int $statusVal of auction
     * @return false| string
     */
    public function getAuctionStatusLabel($statusVal)
    {
        if ($statusVal != '') {
            $options = $this->auctionStatus->getOptionArray();
            return $options[$statusVal];
        }
        return false;
    }

    /**
     * getAucProSoldStatus
     * @param int $auctionId
     * @return false| string
     */
    public function getAucProSoldStatus($auctionId)
    {
        $winBidData = $this->auctionHelperData->getWinnerBidDetail($auctionId);
        $options = $options = ['0'=>__('No'),'1'=>__('Yes')];

        if ($winBidData && $winBidData->getEntityId()) {
            return $options[$winBidData->getShop()];
        }
        return $options[0];
    }

    /**
     * getStatusLabel
     * @param int $value of status
     * @return false| string
     */
    public function getStatusLabel($value)
    {
        if ($value != '') {
            $options = $this->productOptions->getOptionArray();
            return $options[$value];
        }
        return false;
    }

    /**
     * getBidDetailUrl
     * @param int $auctionId
     * @return false| string
     */
    public function getBidDetailUrl($auctionId)
    {
        return $this->getUrl(
            'mpauction/account/auctionbiddetail',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$auctionId
            ]
        );
    }

    /**
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->_scopeConfig->getValue('wk_mpauction/general_settings/enable');
    }
    
    /**
     * @return integer
     */
    public function getUtcOffset($date)
    {
        return timezone_offset_get(new \DateTimeZone($this->_localeDate->getConfigTimezone()), new \DateTime($date));
    }
}
