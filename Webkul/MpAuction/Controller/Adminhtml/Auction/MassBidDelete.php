<?php
/**
 * Webkul MpAuction Bid MassDelete Controller
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
use Webkul\MpAuction\Model\ResourceModel\Amount\CollectionFactory;

class MassBidDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions for delete bid filter.
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
            $recordDeleted = 0;
            $auctionId = 0;
            $data = $this->getRequest()->getParams();
            $auctionId = $this->_session->getAuctionId();
            if (isset($data['selected'])) {
                $bidIds = $data['selected'];
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('entity_id', ['in'=> $bidIds]);
                if ($collection->getSize()) {
                    foreach ($collection as $auctionProductBid) {
                        $auctionId = $auctionProductBid->getAuctionId();
                        $auctionProductBid->setId($auctionProductBid->getEntityId());
                        $this->deleteObj($auctionProductBid);
                        $recordDeleted++;
                    }
                }
            } elseif (isset($data['excluded'])) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('auction_id', ['eq'=> $auctionId]);
                foreach ($collection as $auctionProductBid) {
                    $auctionProductBid->setId($auctionProductBid->getEntityId());
                    $this->deleteObj($auctionProductBid);
                    $recordDeleted++;
                }
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                                        ->setPath('*/*/addauction/', ['id' => $auctionId, 'auction_id' => $auctionId]);
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

    /**
     * Check MpAuction Product bid delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::auc_bid_delete');
    }
}
