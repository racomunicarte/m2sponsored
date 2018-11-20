<?php

namespace Magneto\Sponsored\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Marketplace Sponsored Product Model
 *
 * @method \Magneto\Sponsored\Model\ResourceModel\Sponsored _getResource()
 * @method \Magneto\Sponsored\Model\ResourceModel\Sponsored getResource()
 */
class Sponsored extends AbstractModel
{
    public function _construct() {
        $this->_init('Magneto\Sponsored\Model\ResourceModel\Sponsored');
    }
}
