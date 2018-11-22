<?php
 /**
  * Webkul_MpAuction add Deal layout page.
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
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;

class Index extends \Webkul\Marketplace\Block\Product\Productlist
{
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
        array $data = []
    ) {
        $this->auctionProduct = $auctionProduct;
        parent::__construct($context, $objectManager, $customerSession, $productCollection, $priceCurrency, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $collection = parent::getAllProducts();
            $mpProArray = [0];
            foreach ($collection as $mpProduct) {
                array_push($mpProArray, $mpProduct->getMageproductId());
            }
            $proInAuction = $this->getProductsInAuction();
            $search = $this->getRequest()->getParam('sq');
            $search = filter_var($search, FILTER_SANITIZE_STRING);
            $search = trim($search);
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in'=>$mpProArray])
                ->addFieldToFilter('name', ['like' => '%'.$search.'%'])
                ->addFieldToFilter('entity_id', ['nin'=>$proInAuction])
                ->addFieldToFilter('type_id', ['nin'=> ['grouped', 'configurable']])
                ->addFieldToFilter('visibility', ['neq'=>1]);
            $this->_productList = $collection;
        }
        return $this->_productList;
    }

    /**
     * @param int $productId
     * @return url string add auction on product
     */
    public function getAddAuctionUrl($productId)
    {
        return $this->getUrl(
            'mpauction/account/addauction',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'pid'=>$productId
            ]
        );
    }

    /**
     * Get all products which are not in auction.
     */
    public function getProductsInAuction()
    {
        $auctionProArray = [0];
        $auctionProList = $this->auctionProduct->create()->getCollection()
                                        ->addFieldToFilter('auction_status', ['in' => [0,1]]);
        foreach ($auctionProList as $auctionPro) {
            if ($auctionPro->getProductId()) {
                $auctionProArray[] = $auctionPro->getProductId();
            }
        }
        return $auctionProArray;
    }

    /**
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->_scopeConfig->getValue('wk_mpauction/general_settings/enable');
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
                'auction.product.list.pager'
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
}
