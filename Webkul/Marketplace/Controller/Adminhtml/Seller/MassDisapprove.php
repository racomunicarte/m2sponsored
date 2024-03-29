<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

/**
 * Class MassDisapprove.
 */
class MassDisapprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * @param Context                                     $context
     * @param Filter                                      $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param CollectionFactory                           $collectionFactory
     * @param Processor                                   $productPriceIndexerProcessor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        Processor $productPriceIndexerProcessor
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $allStores = $this->_storeManager->getStores();
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        $customerModel = $this->_objectManager->get(
            'Magento\Customer\Model\Customer'
        );
        $helper = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Data'
        );
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        foreach ($collection as $item) {
            $item->setIsSeller(0);
            $item->setUpdatedAt($this->_date->gmtDate());
            $item->save();
            $sellerProduct = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection()
            ->addFieldToFilter('seller_id', $item->getSellerId());

            if ($sellerProduct->getSize()) {
                $productIds = $sellerProduct->getAllIds();
                $coditionArr = [];
                foreach ($productIds as $key => $id) {
                    $condition = "`mageproduct_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $sellerProduct->setProductData(
                    $coditionData,
                    ['status' => $status]
                );
                foreach ($allStores as $store) {
                    $storeId = $store->getData('store_id');
                    $this->_objectManager->get(
                        'Magento\Catalog\Model\Product\Action'
                    )->updateAttributes(
                        $productIds,
                        ['status' => $status],
                        $storeId
                    );
                }

                $this->_objectManager->get(
                    'Magento\Catalog\Model\Product\Action'
                )->updateAttributes($productIds, ['status' => $status], 0);

                $this->_productPriceIndexerProcessor->reindexList($productIds);
            }

            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $seller = $customerModel->load($item->getSellerId());

            $emailTempVariables['myvar1'] = $seller->getName();
            $emailTempVariables['myvar2'] = $this->_storeManager->getStore()
            ->getUrl(
                'customer/account/login'
            );
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Email'
            )->sendSellerDisapproveMail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $this->_eventManager->dispatch(
                'mp_disapprove_seller',
                ['seller' => $seller]
            );
        }

        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been disapproved.',
                $collection->getSize()
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
