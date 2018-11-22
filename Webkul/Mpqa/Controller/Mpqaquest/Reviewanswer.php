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
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * Webkul Mpqa Mpqaquest Controller.
 */
class Reviewanswer extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $_review;
    
    protected $_customerSession;

    protected $_helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Mpqa\Model\ReviewFactory $reviewFactory
     * @param \Webkul\Mpqa\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpqa\Model\ReviewFactory $reviewFactory,
        \Webkul\Mpqa\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_review=$reviewFactory;
        $this->_customerSession=$customerSession;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(
            'Magento\Customer\Model\Url'
        )->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mpqa page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        
        try {
             $model=$this->_review->create()->getCollection()
                ->addFieldToFilter('answer_id', $data['ansid'])
                ->addFieldToFilter('review_from', $data['custid']);

            foreach ($model as $key) {
                $id=$key->getReviewId();
            }
            if (isset($id)) {
                $model=$this->_review->create()->load($id);
                if ($data['action']=='like') {
                    $model->setLikeDislike('1');
                }
                if ($data['action']=='dislike') {
                    $model->setLikeDislike('0');
                }
                $model->setId($id);
                $model->save();
            } else {
                $model=$this->_review->create();
                $model->setAnswerId($data['ansid'])
                       ->setReviewFrom($data['custid']);
                if ($data['action']=='like') {
                    $model ->setLikeDislike('1');
                }
                if ($data['action']=='dislike') {
                    $model->setLikeDislike('0');
                }
                $model->save();
            }
            $this->_helper->cleanFPC();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
