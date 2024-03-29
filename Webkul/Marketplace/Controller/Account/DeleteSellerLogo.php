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

namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Webkul Marketplace Account DeleteSellerLogo Controller.
 */
class DeleteSellerLogo extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @param Context     $context
     * @param Session     $customerSession
     * @param PageFactory $resultPageFactory
     * @param HelperData  $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        HelperData $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
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
     * DeleteSellerLogo action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            $autoId = '';
            $sellerId = $this->helper->getCustomerId();
            $storeId = $this->helper->getCurrentStoreId();
            $collection = $this->_objectManager
            ->create('Webkul\Marketplace\Model\Seller')
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            )
            ->addFieldToFilter('store_id', $storeId);
            foreach ($collection as $value) {
                $autoId = $value->getId();
            }
            // If seller data doesn't exist for current store
            $fields = [];
            if (!$autoId) {
                $sellerDefaultData = [];
                $collection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('store_id', 0);
                foreach ($collection as $value) {
                    $sellerDefaultData = $value->getData();
                }
                foreach ($sellerDefaultData as $key => $value) {
                    if ($key != 'entity_id') {
                        $fields[$key] = $value;
                    }
                }
            }
            if ($autoId != '') {
                $value = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->load($autoId);
                $value->setLogoPic('');
                $value->setStoreId($storeId);
                $value->save();
            } else {
                $value = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                );
                $value->setData($fields);
                $value->setLogoPic('');
                $value->setStoreId($storeId);
                $value->save();
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode(true)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode($e->getMessage())
            );
        }
    }
}
