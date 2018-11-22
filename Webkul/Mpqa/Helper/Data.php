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
namespace Webkul\Mpqa\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;

/**
 * Webkul Mpqa Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_transportBuilder;
    protected $_inlineTranslation;
    protected $_url;
    protected $httpContext;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Http\Context $httpContext,
        CurrentCustomer $currentCustomer,
        \Magento\Framework\App\Cache\TypeList $cacheTypeList
    ) {
        $this->_storeManager=$storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->httpContext = $httpContext;
        $this->currentCustomer = $currentCustomer;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    public function sendResponseMail($templateVars, $from, $to)
    {
        try{
            $templateOptions = [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
                                'store' => $this->_storeManager->getStore()->getId()
                            ];
            $this->_inlineTranslation->suspend();
            $template_id = $this->scopeConfig->getValue(
                    'mpqa/email/seller_response_template',
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
        } catch(\Exception $e) {
            $this->_inlineTranslation->resume();
        }
    }

    /**
     * function to check the login status of customer
     *
     * @return boolean
     */
    public function checkLogin() 
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * get customer id from context variable
     *
     * @return customerId
     */
    public function getCustomerId() {
        $customerId = $this->httpContext->getValue('customer_id');
        return $customerId;
    }

    /**
     * clean cache after submitting question or answer
     *
     * @return void
     */
    public function cleanFPC() {
        $this->cacheTypeList->cleanType('full_page');
    }
}
