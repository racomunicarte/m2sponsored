<?php

namespace Magneto\Sponsored\Model\Product\Source;

class YesNo extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    protected $_options;

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        }
        return $this->_options;
    }

    final public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => __('No')),
            array('value' => '1', 'label' => __('Yes'))
        );
    }

    public static function getOptionArray()
    {
        return [
            '0' => __('No'),
            '1' => __('Yes')
        ];
    }
}
