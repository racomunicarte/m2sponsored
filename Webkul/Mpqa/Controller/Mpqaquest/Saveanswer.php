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
 * Webkul Mpqa Index Controller.
 */
class Saveanswer extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_answer;
    
    protected $_helper;
    
    protected $_mphelper;
    
    protected $customerRepository;
    
    protected $_question;
    
    protected $_product;
    
    protected $_storeManager;
    
    protected $_datetime;
    
    protected $scopeConfig;
    
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\Mpqa\Helper\Data $helper
     * @param \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory
     * @param \Webkul\Mpqa\Model\QuestionFactory $questionFactory
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\Mpqa\Helper\Data $helper,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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
        $this->customerRepository = $customerRepository;
        $this->_resultJsonFactory=$resultJsonFactory;
        $this->_datetime = $datetime;
        $this->scopeConfig = $scopeConfig;
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
        $question=$this->_question->create()->load($data['qid']);
        $seller_id = $question->getSellerId();
        
        if ($data['cid'] == $seller_id && $this->_mphelper->isSeller()) {
            $time = $this->_datetime->date();
            $data['respond_type'] = 'Seller';
            $data['nickname'] = 'Seller';
            $data['time'] = $this->_datetime->date('Y-m-d H:i:s', $time);
            $data['ans'] = strip_tags($data['ans']);
            $model = $this->_answer->create();
            try {
                $model->setQuestionId($data['qid'])
                    ->setRespondFrom($data['cid'])
                    ->setRespondType($data['respond_type'])
                    ->setRespondNickname("Seller")
                    ->setContent($data['ans'])
                    ->setStatus(1)
                    ->setCreatedAt($time);
                $id=$model->save()->getId();
                $data["id"]=$id;
                if (isset($id)) {   //send response mail
                    $data['status'] = true;
                    $this->messageManager->addSuccess(__("Response saved successfully."));
                    //$question=$this->_question->create()->load($data['qid']);
                    $customer_id = $question->getBuyerId();
                    $seller_id = $question->getSellerId();
                    $p_id=$question->getProductId();
                    $adminStoremail = $this->_mphelper->getAdminEmailId();
                    $adminEmail=$adminStoremail? $adminStoremail:$this->_mphelper->getDefaultTransEmailId();
                    $adminUsername = 'Admin';
                    $customers=$this->customerRepository->getById($customer_id);
                    $customer_name=$customers->getFirstName()." ".$customers->getLastName();
                    $product=$this->_product->create()->load($p_id);
                    $product_name=$product->getName();
                    $url = $product->getProductUrl();
                    $msg = __('You have got a new response on your Question.');
                    $templateVars = [
                                        'store' => $this->_storeManager->getStore(),
                                        'customer_name' => $customer_name,
                                        'link'          =>  $url,
                                        'product_name'  => $product_name,
                                        'message'   => $msg
                                    ];
                    $to = [$customers->getEmail()];
                    $from = ['email' => $adminEmail, 'name' => 'Admin'];
                    $this->_helper->sendResponseMail($templateVars, $from, $to);
                    // mail to admin
                    $admin_email = $this->scopeConfig->getValue(
                        'mpqa/general_settings/responseto_admin',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    if ($admin_email) {
                        $msg = __('Seller replied to a query on his product.');
                        $customers=$this->customerRepository->getById($seller_id);
                        $customer_name=$customers->getFirstName()." ".$customers->getLastName();
                        $templateVars = [
                                            'store' => $this->_storeManager->getStore(),
                                            'customer_name' => __('Admin'),
                                            'link'          =>  $url,
                                            'product_name'  => $product_name,
                                            'message'   => $msg
                                        ];
                        $to = [$adminEmail];
                        $from = ['email' => $customers->getEmail(), 'name' => $customer_name];
                        $this->_helper->sendResponseMail($templateVars, $from, $to);
                    }
                } else {
                    $this->messageManager->addError(__("Error while saving response."));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__("Error while saving response."));
            }
        } else {
            $this->messageManager->addError(__("You are not authenticated to answer this question."));
            $data['status'] = false;
            $result = $this->_resultJsonFactory->create();
            $result->setData($data);
            return $result;
        }
        $this->_helper->cleanFPC();
        $result = $this->_resultJsonFactory->create();
        $result->setData($data);
        return $result;
    }
}
