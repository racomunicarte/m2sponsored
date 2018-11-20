<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Marketplace Sponsored Product Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Sponsored _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Sponsored getResource()
 */
class Sponsored extends AbstractModel
{
    public function _construct() {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Sponsored');
    }
}
