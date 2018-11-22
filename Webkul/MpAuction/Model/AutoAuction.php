<?php

/**
 * Webkul_MpAuction Auto Auction Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model;

use Webkul\MpAuction\Api\Data\AutoAuctionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class AutoAuction extends AbstractModel implements AutoAuctionInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_mpauto_auction';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mpauto_auction';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mpauto_auction';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAuction\Model\ResourceModel\AutoAuction');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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
    public function setEbayProId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get Amount.
     *
     * @return varchar
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set Amount.
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get WinningPrice.
     *
     * @return varchar
     */
    public function getWinningPrice()
    {
        return $this->getData(self::WINNING_PRICE);
    }

    /**
     * Set WinningPrice.
     */
    public function setWinningPrice($winningPrice)
    {
        return $this->setData(self::WINNING_PRICE, $winningPrice);
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
     * Get Flag.
     *
     * @return varchar
     */
    public function getFlag()
    {
        return $this->getData(self::FLAG);
    }

    /**
     * Set Flag.
     */
    public function setFlag($flag)
    {
        return $this->setData(self::FLAG, $flag);
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
