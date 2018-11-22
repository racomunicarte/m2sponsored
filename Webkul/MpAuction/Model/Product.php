<?php

/**
 * MpAuction Product Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model;

use Webkul\MpAuction\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel implements ProductInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_auction_product';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_auction_product';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_auction_product';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAuction\Model\ResourceModel\Product');
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
     * Get MinAmount.
     *
     * @return varchar
     */
    public function getMinAmount()
    {
        return $this->getData(self::MIN_AMOUNT);
    }

    /**
     * Set MinAmount.
     */
    public function setMinAmount($minAmount)
    {
        return $this->setData(self::MIN_AMOUNT, $minAmount);
    }

    /**
     * Get StartingPrice.
     *
     * @return varchar
     */
    public function getStartingPrice()
    {
        return $this->getData(self::STARTING_PRICE);
    }

    /**
     * Set StartingPrice.
     */
    public function setStartingPrice($startingPrice)
    {
        return $this->setData(self::STARTING_PRICE, $startingPrice);
    }

    /**
     * Get ReservePrice.
     *
     * @return varchar
     */
    public function getReservePrice()
    {
        return $this->getData(self::RESERVE_PRICE);
    }

    /**
     * Set ReservePrice.
     */
    public function setReservePrice($reservePrice)
    {
        return $this->setData(self::RESERVE_PRICE, $reservePrice);
    }

    /**
     * Get AuctionStatus.
     *
     * @return varchar
     */
    public function getAuctionStatus()
    {
        return $this->getData(self::AUCTION_STATUS);
    }

    /**
     * Set AuctionStatus.
     */
    public function setAuctionStatus($auctionStatus)
    {
        return $this->setData(self::AUCTION_STATUS, $auctionStatus);
    }

    /**
     * Get Days.
     *
     * @return varchar
     */
    public function getDays()
    {
        return $this->getData(self::DAYS);
    }

    /**
     * Set Days.
     */
    public function setDays($days)
    {
        return $this->setData(self::DAYS, $days);
    }

    /**
     * Get MaxQty.
     *
     * @return varchar
     */
    public function getMaxQty()
    {
        return $this->getData(self::MAX_QTY);
    }

    /**
     * Set MaxQty.
     */
    public function setMaxQty($maxQty)
    {
        return $this->setData(self::MAX_QTY, $maxQty);
    }

    /**
     * Get MinQty.
     *
     * @return varchar
     */
    public function getMinQty()
    {
        return $this->getData(self::MIN_QTY);
    }

    /**
     * Set MinQty.
     */
    public function setMinQty($minQty)
    {
        return $this->setData(self::MIN_QTY, $minQty);
    }

    /**
     * Get startAuctionTime.
     *
     * @return varchar
     */
    public function getStartAuctionTime()
    {
        return $this->getData(self::START_AUCTION_TIME);
    }

    /**
     * Set StartAuctionTime.
     */
    public function setStartAuctionTime($startAuctionTime)
    {
        return $this->setData(self::START_AUCTION_TIME, $startAuctionTime);
    }

    /**
     * Get StopAuctionTime.
     *
     * @return varchar
     */
    public function getStopAuctionTime()
    {
        return $this->getData(self::STOP_AUCTION_TIME);
    }

    /**
     * Set StopAuctionTime.
     */
    public function setStopAuctionTime($stopAuctionTime)
    {
        return $this->setData(self::STOP_AUCTION_TIME, $stopAuctionTime);
    }

    /**
     * Get IncrementOpt.
     *
     * @return varchar
     */
    public function getIncrementOpt()
    {
        return $this->getData(self::INCREMENT_OPT);
    }

    /**
     * Set IncrementOpt.
     */
    public function setIncrementOpt($incrementOpt)
    {
        return $this->setData(self::INCREMENT_OPT, $incrementOpt);
    }

    /**
     * Get IncrementPrice.
     *
     * @return varchar
     */
    public function getIncrementPrice()
    {
        return $this->getData(self::INCREMENT_PRICE);
    }

    /**
     * Set IncrementPrice.
     */
    public function setIncrementPrice($incrementPrice)
    {
        return $this->setData(self::INCREMENT_PRICE, $incrementPrice);
    }

    /**
     * Get AutoAuctionOpt.
     *
     * @return varchar
     */
    public function getAutoAuctionOpt()
    {
        return $this->getData(self::AUTO_AUCTION_OPT);
    }

    /**
     * Set AutoAuctionOpt.
     */
    public function setAutoAuctionOpt($autoAuctionOpt)
    {
        return $this->setData(self::AUTO_AUCTION_OPT, $autoAuctionOpt);
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
