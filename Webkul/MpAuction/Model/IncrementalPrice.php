<?php

/**
 * Webkul_MpAuction Incremental Price Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model;

use Webkul\MpAuction\Api\Data\IncrementalPriceInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class IncrementalPrice extends AbstractModel implements IncrementalPriceInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_mpauction_incremental_price';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_mpauction_incremental_price';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_mpauction_incremental_price';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpAuction\Model\ResourceModel\IncrementalPrice');
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
    public function setCustomerId($name)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Incval.
     *
     * @return varchar
     */
    public function getIncval()
    {
        return $this->getData(self::INCVAL);
    }

    /**
     * Set Incval.
     */
    public function setIncval($incval)
    {
        return $this->setData(self::INCVAL, $incval);
    }
}
