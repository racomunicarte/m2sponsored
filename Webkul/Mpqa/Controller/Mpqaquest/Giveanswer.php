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
 * Webkul Mpqa Giveanswer Controller.
 */
class Giveanswer extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    private $mhelper;

    protected $_question;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\Marketplace\Helper\Data $MarketplaceHelper,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->mhelper=$MarketplaceHelper;
        $this->_customerSession=$customerSession;
        $this->_question=$questionFactory;
        parent::__construct($context);
    }

    /**
     * Giveanswer page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        if ($this->mhelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpqa_mpqaquest_giveanswer_layout2');
        }
        if ($this->mhelper->isSeller()) {
            $customerId = $this->mhelper->getCustomerId();
            $question = $this->_question->create()->load($this->getRequest()->getParam('id'));
            if ($question->getSellerId()==$customerId) {
                $resultPage->getConfig()->getTitle()->set(__('Give Answer'));

                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'mpqa/mpqaquest/showquestions',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
