<?php
/**
 * Webkul_MpAuction Auto Auction Interface.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Api\Data;

interface AutoAuctionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const AUCTION_ID = 'auction_id';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const AMOUNT = 'amount';
    const WINNING_PRICE = 'winning_price';
    const STATUS = 'status';
    const SHOP = 'shop';
    const FLAG = 'flag';
    const CREATED_AT = 'created_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId);

    /**
     * Get AuctionId.
     *
     * @return varchar
     */
    public function getAuctionId();

    /**
     * Set AuctionId.
     */
    public function setAuctionId($auctionId);

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
     * Get ProductId.
     *
     * @return varchar
     */
    public function getProductId();

    /**
     * Set ProductId.
     */
    public function setEbayProId($productId);

    /**
     * Get Amount.
     *
     * @return varchar
     */
    public function getAmount();

    /**
     * Set Amount.
     */
    public function setAmount($amount);

    /**
     * Get WinningPrice.
     *
     * @return varchar
     */
    public function getWinningPrice();

    /**
     * Set WinningPrice.
     */
    public function setWinningPrice($winningPrice);

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
     * Get Shop.
     *
     * @return varchar
     */
    public function getShop();

    /**
     * Set Shop.
     */
    public function setShop($shop);

    /**
     * Get Flag.
     *
     * @return varchar
     */
    public function getFlag();

    /**
     * Set Flag.
     */
    public function setFlag($flag);

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
