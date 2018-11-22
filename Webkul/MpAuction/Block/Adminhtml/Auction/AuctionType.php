<?php
/**
 * Webkul_MpAuction Product Auction Type Adminhtml Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Adminhtml\Auction;

class AuctionType extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'product/setauctiontype.phtml';

    /**
     * @var string
     */
    private $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context      $context
     * @param \Webkul\MpAuction\Helper\Data                $auctionHelperData
     * @param array                                        $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * getAuctionType
     * @return false|string
     */
    public function getAuctionType()
    {
        $auctionType = $this->coreRegistry->registry('product')->getAuctionType();
        if ($auctionType != '') {
            return $auctionType;
        } else {
            return false;
        }
    }
}
