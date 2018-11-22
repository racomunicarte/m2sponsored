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

/**
 * Webkul Mpqa Mpqaquest Controller.
 */
class Submitanswer extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_answer;

    protected $_question;
    
    protected $_helper;
    
    protected $_mphelper;
    
    protected $_product;

    protected $_customerSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory
     * @param \Webkul\Mpqa\Model\QuestionFactory $questionFactory
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\Mpqa\Helper\Data $helper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\Mpqa\Helper\Data $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_answer=$answerFactory;
        $this->_mphelper=$mphelper;
        $this->_helper=$helper;
        $this->_question=$questionFactory;
        $this->_product=$product;
        $this->_storeManager=$storeManager;
        $this->_resultJsonFactory=$resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->_customerSession = $customerSession;
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
        $time = date('Y-m-d H:i:s');
        if ($data['customer_id'] == $this->_mphelper->getCustomerId()) {
            if ($data['seller_id'] == $data['customer_id']) {
                $data['respond_type'] = 'Seller';
            } else {
                $data['respond_type'] = 'Customer';
            }
            $data['qa_nickname'] = strip_tags($data['qa_nickname']);
            $data['qa_answer'] = strip_tags($data['qa_answer']);
            $model=$this->_answer->create();
            $model->setQuestionId($data['question_id'])
                ->setRespondFrom($data['customer_id'])
                ->setRespondType($data['respond_type'])
                ->setRespondNickname($data['qa_nickname'])
                ->setContent($data['qa_answer'])
                ->setStatus(2)
                ->setCreatedAt($time);
            $id=$model->save()->getId();
            if (isset($id)) {   //send response mail
                $question=$this->_question->create()->load($data['question_id']);
                $customer_id=$question->getBuyerId();
                $p_id=$question->getProductId();
                $adminStoremail = $this->_mphelper->getAdminEmailId();
                $adminEmail=$adminStoremail? $adminStoremail:$this->_mphelper->getDefaultTransEmailId();
                $adminUsername = 'Admin';
                $customers=$this->customerRepository->getById($customer_id);
                $customer_name=$customers->getFirstName()." ".$customers->getLastName();
                $product=$this->_product->create()->load($p_id);
                $product_name=$product->getName();
                $url= $product->getProductUrl();
                $msg= 'You have got a new response on your Question.';
                $templateVars = [
                                'store' => $this->_storeManager->getStore(),
                                'customer_name' => $customer_name,
                                'link'          =>  $url,
                                'product_name'  => $product_name,
                                'message'   => $msg
                            ];
                $to=[$customers->getEmail()];
                $from=$from = ['email' => $adminEmail, 'name' => 'Admin'];
                $this->_helper->sendResponseMail($templateVars, $from, $to);
            }
            $final_array = ['status' => true, 'id' => $id];
        } else {
            // $this->messageManager->addError(__("You are not authenticated to answer this question."));
            $final_array = ['status' => false];
        }
        $this->_helper->cleanFPC();
        $result = $this->_resultJsonFactory->create();
        $result->setData($final_array);
        return $result;
    }
}
