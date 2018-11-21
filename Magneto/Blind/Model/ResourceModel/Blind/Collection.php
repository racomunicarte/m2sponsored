<?php

namespace Magneto\Blind\Model\ResourceModel\Blind;

use \Webkul\Marketplace\Model\ResourceModel\AbstractCollection;

/**
 * Magneto Blind ResourceModel collection
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
        $this->_init('Magneto\Blind\Model\Blind', 'Magneto\Blind\Model\ResourceModel\Blind');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
