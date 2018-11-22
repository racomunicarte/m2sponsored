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

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;
use Webkul\MpAuction\Model\ProductFactory as MpAuctionProductFactory;
use Webkul\MpAuction\Helper\Data as MpAuctionHelper;
use Webkul\MpAuction\Model\IncrementalPriceFactory;

class AddAuction extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MarketplaceProductFactory
     */
    private $marketplaceProductFactory;

    /**
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $mpAuctionProductFactory;

    /**
     * @var Webkul\MpAuction\Helper\Data
     */
    private $mpAuctionHelper;

    /**
     * @param Session                    $customerSession,
     * @param Context                    $context,
     * @param ProductRepositoryInterface $productRepository,
     * @param MarketplaceProductFactory  $marketplaceProductFactory,
     * @param MpAuctionProductFactory    $mpAuctionProductFactory,
     * @param MpAuctionHelper            $mpAuctionHelper,
     * @param array                      $data = []
     */
    public function __construct(
        Session $customerSession,
        Context $context,
        ProductRepositoryInterface $productRepository,
        MarketplaceProductFactory $marketplaceProductFactory,
        MpAuctionProductFactory $mpAuctionProductFactory,
        MpAuctionHelper $mpAuctionHelper,
        IncrementalPriceFactory $incrementalPrice,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->marketplaceProductFactory = $marketplaceProductFactory;
        $this->mpAuctionProductFactory = $mpAuctionProductFactory;
        $this->mpAuctionHelper = $mpAuctionHelper;
        $this->incrementalPrice = $incrementalPrice;
        parent::__construct($context, $data);
    }

    /**
     * getAuctionProduct
     * @return bool|array
     */
    public function getAuctionProduct()
    {
        $auctionId = $this->getRequest()->getParam('aid');
        $productId = $this->getRequest()->getParam('pid');
        $sellerId = $this->mpAuctionHelper->getCurrentCustomerId();
        $magePro = false;
        if ($productId) {
            $product = $this->marketplaceProductFactory->create()->getCollection()
                                ->addFieldToFilter('mageproduct_id', $productId)
                                ->addFieldToFilter('seller_id', $sellerId)->setPageSize(1)->getFirstItem();
            if ($product->getEntityId()) {
                $auctionPro = $this->mpAuctionProductFactory->create()->getCollection()
                                    ->addFieldToFilter('customer_id', $sellerId)
                                    ->addFieldToFilter('product_id', $productId)
                                    ->addFieldToFilter('auction_status', 1)->setPageSize(1)->getFirstItem();
                $magePro = $this->productRepository->getById($productId);
                foreach ($auctionPro->getData() as $key => $value) {
                    $magePro->setData($key, $value);
                }
            }
        }

        if ($auctionId) {
            $auctionPro = $this->mpAuctionProductFactory->create()->load($auctionId);
            if ($auctionPro->getCustomerId() == $sellerId) {
                $magePro = $this->productRepository->getById($auctionPro->getProductId());
                foreach ($auctionPro->getData() as $key => $value) {
                    $magePro->setData($key, $value);
                }
            }
        }
        return $magePro;
    }

    /**
     * getAuctionSaveAction
     * @return string Auction Save Action Url
     */
    public function getAuctionSaveAction()
    {
        $auctionId = $this->getRequest()->getParam('aid');
        $productId = $this->getRequest()->getParam('pid');
        if ($auctionId) {
            $sellerId = $this->mpAuctionHelper->getCurrentCustomerId();
            $auctionPro = $this->mpAuctionProductFactory->create()->load($auctionId);
            if ($auctionPro->getCustomerId() == $sellerId) {
                $productId = $auctionPro->getProductId();
            }
        }
        $url = "";
        if ($productId) {
            $url = $this->getUrl(
                'mpauction/account/saveauction',
                [
                    '_secure' => $this->getRequest()->isSecure(),
                    'id' => $productId
                ]
            );
        }
        return $url;
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
     * getMpAuctionConfig
     * @return array
     */
    public function getMpAuctionConfig()
    {
        return $this->mpAuctionHelper->getAuctionConfiguration();
    }

    /**
     * getIncPriceInArray
     * @return array
     */
    public function getIncPriceInArray($incPrice)
    {
        $incPriceRuleInArray = [];
        if ($incPrice) {
            $incPriceRule = json_decode($incPrice, true);
            if (is_array($incPriceRule)) {
                foreach ($incPriceRule as $key => $price) {
                    $key = explode('-', $key);
                    $incPriceRuleInArray[] = ['from' => $key[0], 'to' => $key[1], 'price' => $price];
                }
            }
        }
        return $incPriceRuleInArray;
    }

    /**
     * getIncPriceInArray
     * @return array
     */
    public function getDefaultIncPriceSetByAdmin()
    {
        $incrementalPriceRuleInArray = [];
        $incrementalPriceRule = $this->incrementalPrice->create()->getCollection()
                                                    ->setPageSize(1)->getFirstItem()->getIncval();
        if ($incrementalPriceRule) {
            $incrementalPriceRule = json_decode($incrementalPriceRule, true);
            foreach ($incrementalPriceRule as $key => $price) {
                $key = explode('-', $key);
                $incrementalPriceRuleInArray[] = ['from' => $key[0], 'to' => $key[1], 'price' => $price];
            }
            return $incrementalPriceRuleInArray;
        }
        return [];
    }

    /**
     * @return boolean
     */
    public function isAuctionEnable()
    {
        $config = $this->getMpAuctionConfig();
        return $config['enable'];
    }

    /**
     * @return string
     */
    public function getConfigTimeZone()
    {
        return $this->_localeDate->getConfigTimezone();
    }

    /**
     * @return integer
     */
    public function getUtcOffset($date)
    {
        return timezone_offset_get(new \DateTimeZone($this->_localeDate->getConfigTimezone()), new \DateTime($date));
    }
    
    /**
     * Return offset of current timezone with GMT in seconds
     *
     * @return int
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->_date->getGmtOffset();
    }
}
