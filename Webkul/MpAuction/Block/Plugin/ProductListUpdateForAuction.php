<?php

/**
 * Webkul_MpAuction ProductListUpdateForAuction plugin.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Block\Plugin;

class ProductListUpdateForAuction
{
    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $auctionHelperData;

    public function __construct(
        \Webkul\MpAuction\Helper\Data $auctionHelperData
    ) {
        $this->auctionHelperData = $auctionHelperData;
    }

    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $list,
        $proceed,
        $product
    ) {
        $auctionDetail = $this->auctionHelperData->getProductAuctionDetail($product);
        return $proceed($product).$auctionDetail;
    }
}
