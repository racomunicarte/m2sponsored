<?php
/**
 * Webkul MpAuction CheckoutCartProductAddBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckoutCartProductAddBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */

    private $messageManager;

    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $auctionProductFactory;

    private $dataHelper;

    /**
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param RequestInterface                          $request
     * @param ManagerInterface                          $messageManager
     * @param StockStateInterface                       $stockStateInterface
     * @param \Webkul\MpAuction\Model\WinnerDataFactory $winnerData
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Webkul\MpAuction\Model\ProductFactory $auctionProductFactory,
        \Webkul\MpAuction\Helper\Data $dataHelper
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->itemFactory = $itemFactory;
        $this->auctionProductFactory = $auctionProductFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Sales quote add item event handler.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getFullActionName() != 'wishlist_index_allcart') {
            $itemId = (int)$this->request->getParam('item');
            $item = $this->itemFactory->create()->load($itemId);
            $proId = $item->getProductId();
            $auctionProduct = $this->auctionProductFactory->create()->getCollection()
                ->addFieldToFilter('product_id', ['eq' => $proId])
                ->addFieldToFilter('auction_status', ['in' => [1,0]])->setPageSize(1)
                ->getFirstItem();
            if (count($auctionProduct->getData()) && !$this->dataHelper->checkByItOptionStatus($proId, $auctionProduct->getEntityId(), $auctionProduct->getReservePrice())) {
                $this->messageManager->addError(__('You can not add auction product to cart.'));
                $this->request->setParam('item', false);
                return $this;
            }
        } else {
            $qtyArr = [];
            $qty = $this->request->getParam('qty');
            foreach ($qty as $itemId => $proQty) {
                $item = $this->itemFactory->create()->load($itemId);
                $proId = $item->getProductId();
                $auctionProduct = $this->auctionProductFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['eq' => $proId])
                    ->addFieldToFilter('auction_status', ['in' => [1,0]])->setPageSize(1)
                    ->getFirstItem();
                if (count($auctionProduct->getData()) && !$this->dataHelper->checkByItOptionStatus($proId, $auctionProduct->getEntityId(), $auctionProduct->getReservePrice())) {
                    $this->messageManager->addError(__('You can not add auction product to cart.'));
                    $item->delete();
                }
            }
        }
        return $this;
    }
}
