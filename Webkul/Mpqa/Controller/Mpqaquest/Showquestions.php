<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpqa
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpqa\Controller\Mpqaquest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Mpqa Mpqaquest Controller.
 */
class Showquestions extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    private $mhelper;
    
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\Marketplace\Helper\Data $MarketplaceHelper,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->mhelper=$MarketplaceHelper;
        parent::__construct($context);
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        if ($this->mhelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpqa_mpqaquest_showquestions_layout2');
        }
        if ($this->mhelper->isSeller()) {
            $resultPage->getConfig()->getTitle()->set(__('Questions'));

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
