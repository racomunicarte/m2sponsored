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
class HistoryRight extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'mpauction/historyright.phtml';

    /**
     * @var \Webkul\Auction\Model\Auction
     */
    private $auctionProduct;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Product                   $product
     * @param \Magento\Catalog\Helper\Image                    $imageHelper
     * @param AuctionProduct                                   $auctionProduct
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Helper\Image $imageHelper,
        AuctionProduct $auctionProduct,
        array $data = []
    ) {
        $this->product = $product;
        $this->imageHelper = $imageHelper;
        $this->auctionProduct = $auctionProduct;
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
    public function getAuctionProDetail()
    {
        $data = $this->getRequest()->getParams();
        $productDetail = false;
        if (!isset($data['id'])) {
            return ;
        }
        $proId = $this->auctionProduct->load($data['id'])->getProductId();
        if ($proId) {
            $product = $this->product->load($proId);
            $productDetail = [
                            'url' => $product->getProductUrl(),
                            'name' => $product->getName(),
                            'image' => $this->imageHelper->init($product, 'category_page_grid')->constrainOnly(false)
                                                    ->keepAspectRatio(true)->keepFrame(false)->resize(400)->getUrl()
            ];
        }
        return $productDetail;
    }
}
