<?php
/**
 * Webkul MpAuction MassCancel Controller
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

class MassCancel extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

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
            $recordCancel = 0;
            foreach ($collection->getItems() as $auctionProduct) {
                if ($auctionProduct->getAuctionStatus() == 0 || $auctionProduct->getAuctionStatus() == 1) {
                    $auctionProduct->setId($auctionProduct->getEntityId());
                    $auctionProduct->setAuctionStatus(5);
                    $auctionProduct->setExpired(1);
                    $this->saveObj($auctionProduct);
                    $recordCancel++;
                }
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been cancel.', $recordCancel));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check Auction Product delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::auc_pro_cancel');
    }

    /**
     * saveObj
     * @param Object
     * @return void
     */
    private function saveObj($object)
    {
        $object->save();
    }
}
