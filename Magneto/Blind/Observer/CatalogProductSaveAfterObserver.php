<?php

namespace Magneto\Blind\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogProductSaveAfterObserver
 * @package Magneto\Blind\Observer
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magneto\Sponsored\Helper\Sponsored
     */

    protected $formKey;

    protected $product;

    protected $cart;

    protected $_productRepositoryInterface;

    protected $_checkoutSession;

    protected $_sessionManager;

    /**
     * CatalogProductSaveAfterObserver constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionManager = $sessionManager;
    }

    /**
     * Product save after event handler.
     * Add Sponsor product to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();

            $objectManagerInterface = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $objectManagerInterface->create('Magento\Customer\Model\Session');

            //Utilize observer product save data, Save Blind Product Data
            if ($product->getIsBlind() && $customer->isLoggedIn()) {

                //Prepare Data for Marketplace Blind Product
                $startTimer = date('Y-m-d H:i:s');
                $duration = $this->scopeConfig->getValue(
                    'marketplace/blind_settings/duration',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $startDate = time();
                $endTimer = date('Y-m-d H:i:s', strtotime('+' . $duration . ' day', $startDate));
                $collection = $this->_objectManager->create('Magneto\Blind\Model\Blind');
                $collection
                    ->setProductId($product->getId())
                    ->setProductName($product->getName())
                    ->setSku($product->getSku())
                    ->setStartDate($startTimer)
                    ->setExpiryDate($endTimer)
                    ->setSellerId($customer->getCustomer()->getId());

                if ($collection->save()) {
                    //Add Sponsor Product to Cart
                    $_product = $this->_productRepositoryInterface->get('BLIND');
                    $params = [
                        'form_key' => $this->formKey->getFormKey(),
                        'product' => $_product->getId(),
                        'qty'   => 1
                    ];

                    $_prod = $this->product->load($_product->getId());
                    $this->cart->addProduct($_prod, $params);
                    $this->cart->save();

                    //Save Blind product id in session variable
                    $this->_sessionManager->setBlindProduct($product->getId());
                }
            }

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

    }
}
