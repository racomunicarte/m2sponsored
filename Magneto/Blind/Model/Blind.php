<?php

namespace Magneto\Blind\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Marketplace Blind Product Model
 *
 * @package Magneto\Blind\Model
 */
class Blind extends AbstractModel
{
    public function _construct() {
        $this->_init('Magneto\Blind\Model\ResourceModel\Blind');
    }
}
