<?php
/**
 * Webkul_MpAuction Purchage Status Options Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model\ResourceModel\Amount\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PurchageStatusOptions implements OptionSourceInterface
{
    /**
     * Get Auction Purchage status type labels array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = ['1' => __('Yes'), '0' => __('No')];

        return $options;
    }

    /**
     * Get Auction Purchage status labels
     * array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);

        return $res;
    }

    /**
     * Get Auction Purchage status type labels array for option element.
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
