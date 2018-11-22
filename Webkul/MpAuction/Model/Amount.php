<?php

/**
 * Webkul MpAuction Amount Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model;

use Webkul\MpAuction\Api\Data\AmountInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Amount extends AbstractModel implements AmountInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_auction_amount';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_auction_amount';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_auction_amount';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAuction\Model\ResourceModel\Amount');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get AuctionId.
     *
     * @return varchar
     */
    public function getAuctionId()
    {
        return $this->getData(self::AUCTION_ID);
    }

    /**
     * Set AuctionId.
     */
    public function setAuctionId($auctionId)
    {
        return $this->setData(self::AUCTION_ID, $auctionId);
    }

    /**
     * Get ProductId.
     *
     * @return varchar
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set ProductId.
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get getCustomerId.
     *
     * @return varchar
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set CustomerId.
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get AuctionAmount.
     *
     * @return varchar
     */
    public function getAuctionAmount()
    {
        return $this->getData(self::AUCTION_AMOUNT);
    }

    /**
     * Set AuctionAmount.
     */
    public function setAuctionAmount($auctionAmount)
    {
        return $this->setData(self::AUCTION_AMOUNT, $auctionAmount);
    }

    /**
     * Get WinningStatus.
     *
     * @return varchar
     */
    public function getWinningStatus()
    {
        return $this->getData(self::WINNING_STATUS);
    }

    /**
     * Set WinningStatus.
     */
    public function setWinningStatus($winningStatus)
    {
        return $this->setData(self::WINNING_STATUS, $winningStatus);
    }

    /**
     * Get Shop.
     *
     * @return varchar
     */
    public function getShop()
    {
        return $this->getData(self::SHOP);
    }

    /**
     * Set Shop.
     */
    public function setShop($shop)
    {
        return $this->setData(self::SHOP, $shop);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
