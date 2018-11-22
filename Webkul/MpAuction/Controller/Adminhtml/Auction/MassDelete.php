<?php
/**
 * Webkul MpAuction MassDelete Controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Controller\Adminhtml\Auction;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpAuction\Model\ResourceModel\Product\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $recordDeleted = 0;
            foreach ($collection->getItems() as $auctionProduct) {
                $auctionProduct->setId($auctionProduct->getEntityId());
                $this->deleteObj($auctionProduct);
                $recordDeleted++;
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                                    ->setPath('*/*/index');
    }

    /**
     * Check Auction Product delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::auc_pro_delete');
    }

    /**
     * deleteObj
     * @param Object
     * @return void
     */
    private function deleteObj($object)
    {
        $object->delete();
    }
}
