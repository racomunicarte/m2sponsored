<?php
/**
 * Webkul_MpAuction Status Options Model.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Model\ResourceModel\Product\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AuctionStatus implements OptionSourceInterface
{
    /**
     * Get Auction status type labels array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            '0' => __('Auction Time End'),
            '1' => __('Process'),
            '2' => __('Winner not defined'),
            '3' => __('Canceled by owner'),
            '4' => __('Complete'),
            '5' => __('Canceled by admin'),
        ];

        return $options;
    }

    /**
     * Get Auction status labels array with empty value for option element.
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
     * Get Auction type labels array for option element.
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
