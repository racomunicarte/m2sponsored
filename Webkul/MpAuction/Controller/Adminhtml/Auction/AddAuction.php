<?php
/**
 * Webkul MpAuction AddAuction Controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Adminhtml\Auction;

use Magento\Framework\Controller\ResultFactory;
use Webkul\MpAuction\Model\ProductFactory as AuctionProductFactory;

class AddAuction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProduct;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Magento\Catalog\Model\Product      $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $product,
        AuctionProductFactory $auctionProduct
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->product = $product;
        $this->auctionProduct = $auctionProduct;
    }
    /**
     * Add New MpAuction Form page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $auctionId = (int) $this->getRequest()->getParam('id');
        $auctionProduct = $this->auctionProduct->create();
        if ($auctionId) {
            $auctionProduct = $auctionProduct->load($auctionId);
            $productName = $this->product->load($auctionProduct->getProductId())->getName();
            if (!$auctionProduct->getEntityId()) {
                $this->messageManager->addError(__('Product no longer exist.'));
                $this->_redirect('mpauction/auction/addauction');
                return;
            }
            if (!$aucId = (int)$this->getRequest()->getParam('auction_id')) {
                $this->_redirect('mpauction/auction/index');
                return;
            }
        }
        $this->_session->setAuctionId('');
        $this->_session->setAuctionId($auctionProduct->getEntityId());
        $this->coreRegistry->register('auction_product', $auctionProduct);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $auctionId ? __('Edit Auction For ').$productName : __('Add New Auction');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
    
    /**
     * permission check
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::add_auction');
    }
}
