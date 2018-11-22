<?php
/**
 * Webkul_MpAuction Winner Status Options Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model\ResourceModel\Amount\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WinnerStatusOptions implements OptionSourceInterface
{
    /**
     * Get Auction Winner status type labels array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = ['1' => __('Win'), '0' => __('Pending')];

        return $options;
    }

    /**
     * Get Auction Winner status labels array
     * with empty value for option element.
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
     * Get Auction Winner status type labels array for option element.
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
