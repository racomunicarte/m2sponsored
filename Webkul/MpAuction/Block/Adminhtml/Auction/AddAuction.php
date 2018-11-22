<?php
/**
 * Webkul_MpAuction Add Auction Form Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Adminhtml\Auction;

class AddAuction extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Webkul\MpAuction\Model\ProductFactory
     */
    private $aucProductFactory;

    /**
     * auction product id.
     *
     * @var \Magento\Framework\Registry
     */
    private $proId = 0;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpAuction\Model\ProductFactory $aucProductFactory,
        array $data = []
    ) {
        $this->aucProductFactory = $aucProductFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Imagegallery Images Edit Block.
     */
    protected function _construct()
    {
        $this->_objectId = 'category_map_id';
        $this->_blockGroup = 'Webkul_MpAuction';
        $this->_controller = 'Adminhtml_Auction';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_MpAuction::add_auction')) {
            $auction = $this->_isReAuctionActive();
            if ($auction['reauction']) {
                $this->buttonList->remove('save');
                $this->buttonList->remove('reset');
                $this->addButton(
                    'reauction',
                    [
                        'label' => __('Re Auction'),
                        'onclick' => 'setLocation(\'' . $this->getReAuctionUrl() . '\')',
                        'class' => 'primary'
                    ],
                    -1
                );
            } elseif (!$auction['auction_status']) {
                $this->buttonList->remove('save');
                $this->buttonList->remove('reset');
            } else {
                $this->buttonList->update('save', 'label', __('Save'));
            }
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded image.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Add Auction');
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Check re auction active or not
     * @return bool
     */
    protected function _isReAuctionActive()
    {
        $aucId = $this->getRequest()->getParam('auction_id');
        $auction=[];
        $auction['reauction'] = false;
        $auction['auction_status'] = 1;
        if ($aucId) {
            $auctionPro = $this->aucProductFactory->create()->load($aucId);
            if ($auctionPro->getEntityId()) {
                $aucStatus = $auctionPro->getAuctionStatus();
                $auction['auction_status'] = $auctionPro->getAuctionStatus();
                $status = $auctionPro->getStatus();
                if (!$auction['auction_status'] && $status) {
                    $this->proId = $auctionPro->getProductId();
                    $auction['reauction'] = true;
                    return true;
                }
            }
        }
        return $auction;
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getReAuctionUrl()
    {
        return $this->getUrl('mpauction/auction/addauction', ['pro_id'=> $this->proId]);
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }
}
