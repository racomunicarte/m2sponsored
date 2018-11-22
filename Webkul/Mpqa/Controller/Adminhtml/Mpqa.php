<?php
 /**
  * Webkul Software.
  *
  * @category  Webkul
  * @package   Webkul_Mpqa
  * @author    Webkul
  * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */
namespace Webkul\Mpqa\Controller\Adminhtml;
 
use Magento\Backend\App\Action;

abstract class Mpqa extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpqa::index');
    }
}
