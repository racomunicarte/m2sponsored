<?php
/**
 * Webkul MpAuction bid delete controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Webkul\MpAuction\Model\Amount;

class DeleteBid extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $customerSession;

    /**
     * @var Webkul\MpAuction\Model\Amount
     */
    private $auctionAmt;

    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    private $helperData;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Amount $auctionAmt
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAuction\Helper\Data $helperData,
        Amount $auctionAmt
    ) {
        $this->customerSession = $customerSession;
        $this->auctionAmt = $auctionAmt;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Auction bid delete controller
     *
     * @return Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        /** @var int $curntCustomerId */
        $curntCustomerId = $this->helperData->getCurrentCustomerId();

        /** @var int $bidId */
        $bidId = $this->_request->getParam('id');

        /** @var Webkul\Auction\Model\Amount $bidRecord */
        $bidRecord = $this->auctionAmt->load($bidId);
        if ($bidRecord->getEntityId() && $curntCustomerId == $bidRecord->getCustomerId()) {
            $bidRecord->delete();
            $this->messageManager->addSuccess(__('Auction bid removed successfuly.'));
        } else {
            $this->messageManager->addError(__('Not permitted.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mpauction/account/'));
    }
}
