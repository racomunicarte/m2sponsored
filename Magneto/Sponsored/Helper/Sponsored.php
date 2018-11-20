<?php
namespace Magneto\Sponsored\Helper;

class Sponsored extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_storeManager;

    protected $_product;

    protected $_quote;

    protected $_quoteManagement;

    protected $_customerFactory;

    protected $_customerRepository;

    protected $_orderService;

    protected $_cart;

    protected $_productFactory;

    protected $_cartRepositoryInterface;

    protected $_cartManagement;

    protected $_productRepositoryInterface;

    /**
     * Sponsorship constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_quote = $quote;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;
        $this->_orderService = $orderService;
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_cartRepositoryInterface = $cartRepository;
        $this->_cartManagement = $cartManagement;
        $this->_productRepositoryInterface = $productRepositoryInterface;

        parent::__construct($context);
    }

    /**
     * Create Offline Order
     * Will be used later with payment gateway integration
     * @param $customer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder($customer)
    {
        $orderData =[
            'currency_id'  => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
            'email'        => $customer->getCustomer()->getEmail(), //buyer email id
            'shipping_address' =>[
                'firstname'    => 'FirstName', //address Details
                'lastname'     => 'LastName',
                'street' => 'Main Street',
                'city' => 'Pheonix',
                'country_id' => 'US',
                'region' => 'Arizona',
                'region_id' => 4,
                'postcode' => '12345',
                'telephone' => '1234567890',
                'fax' => '1234567890'
            ],
            'items'=> [
                ['sku'=>'SPONSOR','qty' => 1]
            ]
        ];

        $store = $this->_storeManager->getStore();
        $cart_id = $this->_cartManagement->createEmptyCart();
        $cart = $this->_cartRepositoryInterface->get($cart_id);
        $cart->setStore($store);
        $customer= $this->_customerRepository->getById($customer->getCustomer()->getId());
        $cart->setCurrency();
        $cart->assignCustomer($customer); //Assign quote to customer

        foreach($orderData['items'] as $item) {
            $product = $this->_productRepositoryInterface->get($item['sku']);
            $cart->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        $cart->getBillingAddress()->addData($orderData['shipping_address']);
        $cart->getShippingAddress()->addData($orderData['shipping_address']);
        $shippingAddress = $cart->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('freeshipping_freeshipping');
        $cart->setPaymentMethod('checkmo');
        $cart->setInventoryProcessed(false);
        $cart->getPayment()->importData(['method' => 'checkmo']);
        $cart->collectTotals();

        $cart->save();
        $cart = $this->_cartRepositoryInterface->get($cart->getId());
        $orderId = $this->_cartManagement->placeOrder($cart->getId());

        if ($orderId) {
            return true;
        }

        return false;
    }

    /**
     * Identify Sponsored Product
     * Compare Expiry date with current date
     *
     * @param $product
     * @return bool
     */
    public function validateSponsoredProduct($product)
    {
        $filterDate = date('Y-m-d', strtotime($product->getExpiryDate()));
        $currentDate = date('Y-m-d');
        if ($currentDate <= $filterDate) {
            return true;
        }

        return false;
    }

    public function getSponsoredOptionArray()
    {
        return $this->_objectManager->create(
            'Magneto\Sponsored\Model\Product\Source\YesNo'
        )->getOptionArray();
    }
}

?>