<?php
/**
 * Webkul_MpAuction Navigation Block
 *
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Account;

/**
 * MpAuction Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @return string current url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @return boolean
     */
    public function isAuctionEnable()
    {
        return $this->_scopeConfig->getValue('wk_mpauction/general_settings/enable');
    }

    /**
     * getMpAuction for get complete url using url key
     * @param String $urlKey
     * @return String url
     */
    public function getDealUrl($urlKey)
    {
        return $this->getUrl($urlKey, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getCurrentNavClass return nav item active or not class
     * @param  string $urlKey url key for match with current url
     * @return string|""
     */
    public function getCurrentNavClass($urlKey)
    {
        $currentUrl = $this->getCurrentUrl();
        return strpos($currentUrl, $urlKey) && !strpos($currentUrl, 'mpauction/account/autobidding/')
                    && !strpos($currentUrl, 'mpauction/account/bidsrecords/') ? "current" : "";
    }
}
