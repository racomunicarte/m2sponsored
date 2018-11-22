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

namespace Webkul\Mpqa\Controller\Adminhtml\Mpqa;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;

class Giveanswer extends Action
{
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $resultPageFactory;

    protected $_answer;

    protected $_timezone;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    protected $_qahelper;

    protected $_mphelper;
    
    protected $customerRepository;

    protected $_question;

    protected $_product;

    protected $_storeManager;
    /**
     * @param Context       $context
     * @param PageFactory   $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Timezone $timezone,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Mpqa\Helper\Data $qahelper,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_qahelper = $qahelper;
        $this->_mphelper = $mphelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_answer = $answerFactory;
        $this->_question = $questionFactory;
        $this->customerRepository = $customerRepository;
        $this->_product = $product;
        $this->_storeManager = $storeManager;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * Giveanswer page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data=$this->getRequest()->getParams();
        $time = date('Y-m-d H:i:s');
        $data['respond_type']='Admin';
        $data['nickname']='Admin';
        $data['time']= $this->_timezone->formatDate($time,\IntlDateFormatter::MEDIUM,true);
        $data['content'] = strip_tags($data['content']);
        $model=$this->_answer->create();
        $model->setQuestionId($data['pid'])
            ->setRespondFrom(0)
            ->setRespondType($data['respond_type'])
            ->setRespondNickname($data['nickname'])
            ->setContent($data['content'])
            ->setStatus(1)
            ->setCreatedAt($time);
        $id=$model->save()->getId();
        $data['answer_id']=$id;
        if (isset($id)) {   //send response mail
            $question=$this->_question->create()->load($data['pid']);
            $customer_id=$question->getBuyerId();
            $seller_id = $question->getSellerId();
            $p_id=$question->getProductId();
            $adminStoremail = $this->_mphelper->getAdminEmailId();
            $adminEmail=$adminStoremail? $adminStoremail:$this->_mphelper->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $customers=$this->customerRepository->getById($customer_id);
            $customer_name=$customers->getFirstName()." ".$customers->getLastName();
            $product=$this->_product->create()->load($p_id);
            $product_name=$product->getName();
            $url= $product->getProductUrl();
            $msg= __('You have got a new response on your question.');
            $templateVars = [
                                'store' => $this->_storeManager->getStore(),
                                'customer_name' => $customer_name,
                                'link'          =>  $url,
                                'product_name'  => $product_name,
                                'message'   => $msg
                            ];
            $to = [$customers->getEmail()];
            $from = ['email' => $adminEmail, 'name' => 'Admin'];
            $this->_qahelper->sendResponseMail($templateVars, $from, $to);
            
            $seller_email = $this->scopeConfig->getValue(
                'mpqa/general_settings/responseto_seller',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($seller_id && $seller_email) { //mail to seller
                $customers=$this->customerRepository->getById($seller_id);
                $customer_name=$customers->getFirstName()." ".$customers->getLastName();
                $msg= __('Admin replied to a query on your product.');
                $templateVars = [
                                    'store' => $this->_storeManager->getStore(),
                                    'customer_name' => $customer_name,
                                    'link'          =>  $url,
                                    'product_name'  => $product_name,
                                    'message'   => $msg
                                ];
                $to = [$customers->getEmail()];
                $from = ['email' => $adminEmail, 'name' => 'Admin'];
                $this->_qahelper->sendResponseMail($templateVars, $from, $to);
            }
        }
        $result = $this->_resultJsonFactory->create();
        $result->setData($data);
        return $result;
    }
}
