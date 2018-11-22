<?php

/**
 * Webkul_MpAuction Detail tab block
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Block\Adminhtml\Auction\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder,
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Framework\Registry              $coreRegistry
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('auction_product_tabs');
        $this->setDestElementId('auction_product_section');
        $this->setTitle(__('Auction Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'auction_form',
            [
                'label' => __('Auction Product'),
                'title' => __('Auction Product'),
                'content' => $this->getChildHtml('auction_form'),
                'active' => true
            ]
        );
        $model = $this->_coreRegistry->registry('auction_product');
        if ($model->getEntityId()) {
            $this->addTab(
                'normal_bid',
                [
                    'label' => __('Bid List'),
                    'title' => __('Bid List'),
                    'content' => $this->getChildHtml('normal_bid'),
                    
                ]
            );
            $this->addTab(
                'auto_bid_list',
                [
                    'label' => __('Auto Bid List'),
                    'title' => __('Auto Bid List'),
                    'content' => $this->getChildHtml('auto_bid_list')
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}
