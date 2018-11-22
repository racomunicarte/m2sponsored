<?php

namespace Webkul\MpAuction\Controller\Account;

/**
 * Webkul_MpAuction deal MassDisable controller
 * @category  Webkul
 * @package   Webkul_MpAuctions
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProductFactory;
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;

class MassDisable extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\Auction\Model\ProductFactory
     */
    private $auctionProduct;

    /**
     * @var MarketplaceProductFactory
     */
    private $marketplaceProductFactory;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @param Context                         $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AuctionProduct                  $auctionProduct
     * @param MarketplaceProductFactory       $mpProductFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        MarketplaceProductFactory $mpProductFactory,
        \Webkul\MpAuction\Helper\Data $helperData,
        AuctionProduct $auctionProduct
    ) {
        $this->customerSession = $customerSession;
        $this->marketplaceProductFactory = $mpProductFactory;
        $this->auctionProduct = $auctionProduct;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Auction massDisable
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['auction_mass_disable'])) {
            $mpAucProList = $this->auctionProduct->create()->getCollection()
                                    ->addFieldToFilter('entity_id', ['in' => $data['auction_mass_disable']]);
            $recordCancel = 0;
            foreach ($mpAucProList as $mpAucPro) {
                $isSeller = $this->checkIsSellerProduct($mpAucPro->getProductId());
                if (($mpAucPro->getAuctionStatus() == 0 || $mpAucPro->getAuctionStatus() == 1) && $isSeller) {
                    $mpAucPro->setId($mpAucPro->getEntityId());
                    $mpAucPro->setAuctionStatus(3);
                    $mpAucPro->setExpired(1);
                    $this->saveObj($mpAucPro);
                    $recordCancel++;
                }
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been canceled successfully.', $recordCancel));
        } else {
            $this->messageManager->addError(__('Invalid request.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mpauction/account/auctionlist'));
    }

    /**
     * saveObj
     * @param Object
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }

    /**
     * @param int $productId
     * @return false | int
     */
    private function checkIsSellerProduct($productId)
    {
        $sellerPro = $this->marketplaceProductFactory->create()->getCollection($productId)
                                ->addFieldToFilter('mageproduct_id', $productId)
                                ->addFieldToFilter('seller_id', $this->helperData->getCurrentCustomerId())
                                ->setPageSize(1)->getFirstItem();
        return $sellerPro->getEntityId() ? $sellerPro->getEntityId() : false;
    }
}
