<?php
/**
 * Webkul_MpAuction Incremental Price Interface.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Api\Data;

interface IncrementalPriceInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const INCVAL = 'incval';

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
     * Get Incval.
     *
     * @return varchar
     */
    public function getIncval();

    /**
     * Set Incval.
     */
    public function setIncval($incval);
}
