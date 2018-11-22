<?php
/**
 * Webkul_MpAuction DetailContainer MpAuction Adminhtml Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Adminhtml\Auction;

class DetailContainer extends \Magento\Backend\Block\Template
{
    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $auctionConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context      $context
     * @param \Webkul\MpAuction\Helper\Data                $auctionHelperData
     * @param array                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpAuction\Helper\Data $auctionHelperData,
        array $data = []
    ) {
        $this->auctionConfig = $auctionHelperData->getAuctionConfiguration();
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        $script = $this->auctionConfig['reserve_enable'] ? '' : '<script type="text/x-magento-init">
                            {"body": {"forAutoAuction": {}}}</script>';
        return '<div id="auction_product_section"></div>'.$script;
    }
}
