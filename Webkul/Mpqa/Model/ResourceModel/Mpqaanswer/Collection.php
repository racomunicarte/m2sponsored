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
namespace Webkul\Mpqa\Model\ResourceModel\Mpqaanswer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Webkul\Mpqa\Model\Mpqaanswer', 'Webkul\Mpqa\Model\ResourceModel\Mpqaanswer');
        $this->_map['fields']['entity_id'] = 'main_table.answer_id';
    }
}
