<?php
/**
 * Webkul_MpAuction All Products For Auction.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model\ResourceModel\Product\Source;

class AllProductsForAuction
{
    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    private $productFactory;

    /**
     * @param \Webkul\MpAuction\Model\Product $auctionProduct
     */
    private $auctionProduct;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory
    ) {
        $this->productFactory = $productFactory;
        $this->auctionProduct = $auctionProductFactory;
    }

    /**
     * Return options array.
     *
     * @param int $productId
     * @return array
     */
    public function productListForAuction($productId)
    {
        $productArr[] = ['value' => '','label' => 'Select Product'];
        $auctionProducts = $this->getProductsInAuction();
        $productList = $this->productFactory->create()->getCollection()->addAttributeToSelect('*')
                                    ->addFieldToFilter('type_id', ['neq' => 'bundle'])
                                    ->addFieldToFilter('type_id', ['neq' => 'grouped'])
                                    ->addFieldToFilter('auction_type', ['like' => '%'.'2'.'%']);
        if ($productId) {
            $productArr = [];
            $productList->addFieldToFilter('entity_id', ['eq' => $productId]);
        } else {
            $productList->addFieldToFilter('entity_id', ['nin' => $auctionProducts]);
        }
        foreach ($productList as $product) {
            $productArr[] = [
                'value' => $product->getEntityId(),
                'label' => $product->getName().' ( '.$product->getSku().' )'
            ];
        }
        return $productArr;
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        $optionList = $this->toOptionArray();
        $optionArray = [];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }

        return $optionArray;
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
}
