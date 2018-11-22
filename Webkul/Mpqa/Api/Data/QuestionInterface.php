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
namespace Webkul\Mpqa\Api\Data;

interface QuestionInterface
{
   
    const ENTITY_ID    = 'id';
    public function getId();
    public function setId($id);
    // public function deleteByProduct($product_id);
}
