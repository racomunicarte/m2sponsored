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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Mpqa Mpqaquest controller.
 */
class Savequestion extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Customer
     */
    protected $_customer;

    protected $_helper;

    /**
     * @var Product
     */
    protected $_product;

    protected $_question;

    protected $_storeManager;
    protected $_transportBuilder;
    protected $_inlineTranslation;
    protected $_url;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    protected $qaHelper;

    /**
     * @param Context   $context
     * @param Session   $customerSession
     * @param Customer  $customer
     * @param Product   $product
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Customer $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Mpqa\Helper\Data $qaHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_customer = $customer;
        $this->_product = $product;
        $this->_customerSession = $customerSession;
        $this->_question=$questionFactory;
        $this->_storeManager=$storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->customerRepository = $customerRepository;
        $this->_helper=$helper;
        $this->_resultJsonFactory=$resultJsonFactory;
        $this->_url=$context->getUrl();
        $this->qaHelper = $qaHelper;
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
     * Savequestion Action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $time = date('Y-m-d H:i:s');
            $data['buyer_id'] = $this->_customerSession->getId();
            if ($this->scopeConfig->getValue(
                'mpqa/general_settings/question_approval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )) {
                $data['status']=0;
            } else {
                $data['status']=1;
            }
            $data['subj'] = strip_tags($data['subj']);
            $data['con'] = strip_tags($data['con']);
            $data['nickname'] = strip_tags($data['nickname']);
            $model = $this->_question->create();
            $data['created_time'] = $time;
            $model->setBuyerId($data['buyer_id']);
            $model->setSellerId($data['seller_id']);
            $model->setSubject($data['subj']);
            $model->setContent($data['con']);
            $model->setProductId($data['pid']);
            $model->setQaNickname($data['nickname']);
            $model->setStatus($data['status']);
            $model->setCreatedAt($time);
            $result['qid']=$model->save()->getId();
            $result['status'] = 1;
           
            //Send Mail
            $mail_to_admin=$this->scopeConfig->getValue(
                'mpqa/general_settings/admin_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $mail_to_seller=$this->scopeConfig->getValue(
                'mpqa/general_settings/seller_email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $this->qaHelper->cleanFPC();

            if (isset($result['qid'])) {
                $adminStoremail = $this->_helper->getAdminEmailId();
                $adminEmail=$adminStoremail? $adminStoremail:$this->_helper->getDefaultTransEmailId();
                $adminUsername = 'Admin';
                
                if ($data['seller_id']!=0) {
                    $seller = $this->customerRepository->getById($data['seller_id']);
                    $seller_name=$seller->getFirstName()." ".$seller->getLastName();
                    $seller_email=$seller->getEmail();
                    $url= $this->_url->getUrl('mpqa/mpqaquest/giveanswer').'id/'.$result['qid'];
                } else {
                    $seller_name='Admin';
                    $seller_email=$adminEmail;
                    $url= 'Please login to view and give response';
                }
                
                $customers=$this->customerRepository->getById($data['buyer_id']);
                $customer_name=$customers->getFirstName()." ".$customers->getLastName();
                $product=$this->_product->create()->load($data['pid']);
                $product_name=$product->getName();
                $msg= 'I would like to inform that '.$customer_name.' asked a question on your product. ';
                $templateVars = [
                                'store' => $this->_storeManager->getStore(),
                                'customer_name' => $customer_name,
                                'seller_name'   => $seller_name,
                                'link'          =>  $url,
                                'product_name'  => $product_name,
                                'message'   => $msg
                            ];
                $to = [$seller_email];
                if (($data['seller_id']!=0) && ($mail_to_seller)) {
                    $this->sendSellerMail($templateVars, $adminEmail, $to);
                    if ($mail_to_admin) {
                        $templateVars['seller_name']='Admin';
                        $templateVars['link']='Please login to view and give response';
                        $templateVars['message']='I would like to inform that '.$customer_name.' asked a question on seller product. ';
                        $this->sendAdminMail($templateVars, $adminEmail, $adminEmail);
                    }
                    
                } elseif (($data['seller_id']==0) && ($mail_to_admin)) {
                    $this->sendAdminMail($templateVars, $adminEmail, $to);
                }
            }
            
            $arr = $this->_resultJsonFactory->create();
            $arr->setData($result);
            return $arr;
        }
    }

    public function sendSellerMail($templateVars, $adminEmail, $to)
    {
        try{
            $from = ['email' => $adminEmail, 'name' => 'Admin'];
            $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId()];
            $this->_inlineTranslation->suspend();
            $template_id=$this->scopeConfig->getValue(
                    'mpqa/email/askproductquery_seller_template',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($to)
                    ->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e){
            $this->_inlineTranslation->resume();
        }
    }

    public function sendAdminMail($templateVars, $adminEmail, $to)
    {
        try{
            $from = ['email' => $adminEmail, 'name' => 'Admin'];
            
            $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId()];
            $this->_inlineTranslation->suspend();
            $template_id=$this->scopeConfig->getValue(
                    'mpqa/email/askproductquery_admin_template',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            $transport = $this->_transportBuilder->setTemplateIdentifier($template_id)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($to)
                    ->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
        }
    }
}
