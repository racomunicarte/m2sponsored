<?php
/**
 * Webkul_MpAuction Product Interface.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Api\Data;

interface ProductInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const MIN_AMOUNT = 'min_amount';
    const STARTING_PRICE = 'starting_price';
    const RESERVE_PRICE = 'reserve_price';
    const AUCTION_STATUS = 'auction_status';
    const DAYS = 'days';
    const MAX_QTY = 'max_qty';
    const MIN_QTY = 'min_qty';
    const START_AUCTION_TIME = 'start_auction_time';
    const STOP_AUCTION_TIME = 'stop_auction_time';
    const INCREMENT_OPT = 'increment_opt';
    const INCREMENT_PRICE = 'increment_price';
    const AUTO_AUCTION_OPT = 'auto_auction_opt';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId();

    /**
     * Set EntityId.
     */
    public function setId($id);

    /**
     * Get ProductId.
     *
     * @return varchar
     */
    public function getProductId();

    /**
     * Set ProductId.
     */
    public function setProductId($productId);

    /**
     * Get getCustomerId.
     *
     * @return varchar
     */
    public function getCustomerId();

    /**
     * Set CustomerId.
     */
    public function setCustomerId($name);

    /**
     * Get MinAmount.
     *
     * @return varchar
     */
    public function getMinAmount();

    /**
     * Set MinAmount.
     */
    public function setMinAmount($minAmount);

    /**
     * Get StartingPrice.
     *
     * @return varchar
     */
    public function getStartingPrice();

    /**
     * Set StartingPrice.
     */
    public function setStartingPrice($startingPrice);

    /**
     * Get ReservePrice.
     *
     * @return varchar
     */
    public function getReservePrice();

    /**
     * Set ReservePrice.
     */
    public function setReservePrice($reservePrice);

    /**
     * Get AuctionStatus.
     *
     * @return varchar
     */
    public function getAuctionStatus();

    /**
     * Set AuctionStatus.
     */
    public function setAuctionStatus($auctionStatus);

    /**
     * Get Days.
     *
     * @return varchar
     */
    public function getDays();

    /**
     * Set Days.
     */
    public function setDays($days);

    /**
     * Get MaxQty.
     *
     * @return varchar
     */
    public function getMaxQty();

    /**
     * Set MaxQty.
     */
    public function setMaxQty($maxQty);

    /**
     * Get MinQty.
     *
     * @return varchar
     */
    public function getMinQty();

    /**
     * Set MinQty.
     */
    public function setMinQty($minQty);

    /**
     * Get startAuctionTime.
     *
     * @return varchar
     */
    public function getStartAuctionTime();

    /**
     * Set StartAuctionTime.
     */
    public function setStartAuctionTime($startAuctionTime);

    /**
     * Get StopAuctionTime.
     *
     * @return varchar
     */
    public function getStopAuctionTime();

    /**
     * Set StopAuctionTime.
     */
    public function setStopAuctionTime($stopAuctionTime);

    /**
     * Get IncrementOpt.
     *
     * @return varchar
     */
    public function getIncrementOpt();

    /**
     * Set IncrementOpt.
     */
    public function setIncrementOpt($incrementOpt);

    /**
     * Get IncrementPrice.
     *
     * @return varchar
     */
    public function getIncrementPrice();

    /**
     * Set IncrementPrice.
     */
    public function setIncrementPrice($incrementPrice);

    /**
     * Get AutoAuctionOpt.
     *
     * @return varchar
     */
    public function getAutoAuctionOpt();

    /**
     * Set AutoAuctionOpt.
     */
    public function setAutoAuctionOpt($autoAuctionOpt);

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus();

    /**
     * Set Status.
     */
    public function setStatus($status);

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt);
}
