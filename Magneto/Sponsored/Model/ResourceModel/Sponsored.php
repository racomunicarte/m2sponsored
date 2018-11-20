<?php
/**
 * Magneto Sponsored.
 *
 * @category   Magento
 * @package   Magneto_Sponsored
 * @author    Magneto
 */

namespace Magneto\Sponsored\Model\ResourceModel;

/**
 * Class Sponsored
 * @package Magneto\Sponsored\Model\Resource
 */
class Sponsored extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        /* Custom Table Name */
        $this->_init('marketplace_sponsored_products','entity_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && ($field === null)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }
}