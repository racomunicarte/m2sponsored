<?php
/**
 * Webkul_MpAuction Amount Interface.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Api\Data;

interface AmountInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const AUCTION_ID = 'auction_id';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const AUCTION_AMOUNT = 'auction_amount';
    const WINNING_STATUS = 'winning_status';
    const SHOP = 'shop';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

   /**
    * Get AuctionId.
    *
    * @return string
    */
    public function getAuctionId();

   /**
    * set AuctionId.
    *
    * @return $this
    */
    public function setAuctionId($auctionId);

   /**
    * Get ProductId.
    *
    * @return string
    */
    public function getProductId();

   /**
    * set ProductId.
    *
    * @return $this
    */
    public function setProductId($productId);

   /**
    * Get CustomerId.
    *
    * @return string
    */
    public function getCustomerId();

   /**
    * set CustomerId.
    *
    * @return $this
    */
    public function setCustomerId($customerId);

    /**
     * Get AuctionAmount.
     *
     * @return varchar
     */
    public function getAuctionAmount();

    /**
     * Set AuctionAmount.
     */
    public function setAuctionAmount($auctionAmount);

    /**
     * Get WinningStatus.
     *
     * @return varchar
     */
    public function getWinningStatus();

    /**
     * Set WinningStatus.
     */
    public function setWinningStatus($winningStatus);

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
    * @return string
    */
    public function getCreatedAt();

   /**
    * set CreatedAt.
    *
    * @return $this
    */
    public function setCreatedAt($createdAt);
}
