<?php

/**
 * Webkul_MpAuction IncrementalPriceRange MpAuction Form Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Webkul\MpAuction\Model\IncrementalPrice;

class IncrementalPriceRange extends FormField
{
    const BUTTON_TEMPLATE = 'system/config/button/incrementalPriceRange.phtml';

    /**
     * @var \Webkul\MpAuction\Model\IncrementalPrice
     */
    private $incrementalPrice;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        IncrementalPrice $incrementalPrice,
        array $data = []
    ) {
        $this->incrementalPrice = $incrementalPrice;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself.
     * @return $this
     */

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button.
     * @param AbstractElement $element
     * @return string
     */

    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */

    public function getAjaxCheckUrl()
    {
        return $this->getUrl('mpauction/config/incrementalpricesave');
    }

    public function incrementalPriceRule()
    {
        $incrementalPriceRuleInArray = [];
        $incrementalPriceRule = $this->incrementalPrice->getCollection()->setPageSize(1)->getFirstItem()->getIncval();
        if ($incrementalPriceRule) {
            $incrementalPriceRule = json_decode($incrementalPriceRule, true);
            foreach ($incrementalPriceRule as $key => $price) {
                $key = explode('-', $key);
                $incrementalPriceRuleInArray[] = ['from' => $key[0], 'to' => $key[1], 'price' => $price];
            }
            return $incrementalPriceRuleInArray;
        }
        return [];
    }

    /**
     * Get the button and scripts contents.
     * @param AbstractElement $element
     * @return string
     */
    
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'wk_mpauction_incremental_price_button',
                'button_label' => __('Set Incremental Price Range'),
                'onclick' => 'javascript:check(); return false;',
            ]
        );
        return $this->_toHtml();
    }
}
