<?php
/**
 * Webkul MpAuction CatalogProductSaveAfter Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    /**
     * @param \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory
     */
    public function __construct(
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory
    ) {
        $this->auctionProductFactory = $auctionProductFactory;
    }

    /**
     * product save event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getDataObject();
        $auctionOpt = $product->getAuctionType();
        $auctionOpt = explode(',', $auctionOpt);
        $productId = $product->getId();
        $auctionProduct = $this->auctionProductFactory->create()->getCollection()
                                    ->addFieldToFilter('product_id', ['eq' => $product->getId()])
                                    ->setPageSize(1)->getFirstItem();
        return $this;
    }
}
