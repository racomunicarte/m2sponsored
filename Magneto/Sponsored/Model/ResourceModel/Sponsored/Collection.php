<?php

namespace Magneto\Sponsored\Model\ResourceModel\Sponsored;

use \Webkul\Marketplace\Model\ResourceModel\AbstractCollection;

/**
 * Magneto Sponsored ResourceModel Sponsored collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magneto\Sponsored\Model\Sponsored', 'Magneto\Sponsored\Model\ResourceModel\Sponsored');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
