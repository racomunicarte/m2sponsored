<?php
/**
 * Webkul_MpAuction Detail block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Account;

/**
 * Auction detail block
 */
class AucAutoBidHistory extends \Webkul\MpAuction\Block\AutoBidHistory
{
    /**
     * @param int $customerId
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $config = $this->getAuctionConfig();
        $customerModel = $this->getCustomerModel();
        return $customerModel->load($customerId)->getName();
    }

    /**
     * get Formated price
     * @param $amount float
     * @return string
     */
    public function formatPrice($amount)
    {
        $config = $this->getAuctionConfig();
        $priceHelper = $this->getPriceHelper();
        return $priceHelper->currency($amount, true, false);
    }
}
