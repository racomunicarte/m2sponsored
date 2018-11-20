<?php

namespace Magneto\Sponsored\Observer;

use Magento\Framework\Event\ObserverInterface;
//use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul Marketplace CatalogProductSaveAfterObserver Observer.
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    //protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magneto\Sponsored\Helper\Sponsored
     */
    //protected $_sponsorsedHelper;

    protected $formKey;

    protected $product;

    protected $cart;

    protected $_productRepositoryInterface;

    /**
     * CatalogProductSaveAfterObserver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magneto\Sponsored\Helper\Sponsored $sponsorsedHelper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Product $product
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        //\Magneto\Sponsored\Helper\Sponsored $sponsorsedHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
        //\Zend\Validator\Uri $uri,
        //\Magento\Framework\App\ResponseFactory $responseFactory,
        //\Magento\Framework\App\RequestInterface $request
        //CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        //$this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
        $this->scopeConfig = $scopeConfig;
        //$this->_sponsorsedHelper = $sponsorsedHelper;
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->_productRepositoryInterface = $productRepositoryInterface;
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

            //Utilize observer product save data, Save Sponsored Product Data
            if ($product->getIsSponsored() && $customer->isLoggedIn()) {

                //Prepare Data for Marketplace Sponsored Product
                $startTimer = date('Y-m-d H:i:s');
                $duration = $this->scopeConfig->getValue(
                    'marketplace/sponsored_settings/duration',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $startDate = time();
                $endTimer = date('Y-m-d H:i:s', strtotime('+' . $duration . ' day', $startDate));
                $sponsoredCollection = $this->_objectManager->create('Magneto\Sponsored\Model\Sponsored');
                $sponsoredCollection
                    ->setProductId($product->getId())
                    ->setProductName($product->getName())
                    ->setSku($product->getSku())
                    ->setStartDate($startTimer)
                    ->setExpiryDate($endTimer)
                    ->setSellerId($customer->getCustomer()->getId());

                if ($sponsoredCollection->save()) {
                    //Add Sponsor Product to Cart
                    $product = $this->_productRepositoryInterface->get('SPONSOR');
                    $params = [
                        'form_key' => $this->formKey->getFormKey(),
                        'product' => $product->getId(),
                        'qty'   => 1
                    ];

                    $_product = $this->product->load($product->getId());
                    $this->cart->addProduct($_product, $params);
                    $this->cart->save();
                }
            }

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

    }
}
