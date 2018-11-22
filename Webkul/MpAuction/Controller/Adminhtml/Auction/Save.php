<?php
/**
 * Webkul MpAuction Save Controller
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Adminhtml\Auction;

class Save extends \Magento\Backend\App\Action
{
    /**
     *
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localTime;

    /**
     *
     * @var Webkul\MpAuction\Model\ProductFactory
     */
    private $aucProduct;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Magento\Catalog\Model\Product      $product
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localTime,
        \Webkul\MpAuction\Model\ProductFactory $aucProduct
    ) {
        parent::__construct($context);
        $this->localTime = $localTime;
        $this->aucProduct = $aucProduct;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('mpauction/auction/addauction');
            return;
        }
        try {
            $defaultZone = $this->localTime->getDefaultTimezone();
            $configZone = $this->localTime->getConfigTimezone();

            $auctionProduct = $this->aucProduct->create();
            $data['start_auction_time'] = $this->converToTz($data['start_auction_time'], $defaultZone, $configZone);
            $data['stop_auction_time'] = $this->converToTz($data['stop_auction_time'], $defaultZone, $configZone);
            $data['min_amount'] = $data['starting_price'];
            $auctionProduct->setData($data);
            if (isset($data['entity_id'])) {
                $auctionProduct->setEntityId($data['entity_id']);
            } else {
                $mpExpAucProducts = $this->aucProduct->create()->getCollection()
                                                        ->addFieldToFilter('product_id', $data['product_id'])
                                                        ->addFieldToFilter('expired', 0);
                foreach ($mpExpAucProducts as $expProduct) {
                    $expProduct->setExpired(1);
                    $this->saveObj($expProduct);
                }
                $auctionProduct->setAuctionStatus(1);
                $auctionProduct->setStatus(0);
            }
            $this->saveObj($auctionProduct);
            $this->messageManager->addSuccess(__('Auction product has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('mpauction/auction/index');
    }

    /**
     * convert Datetime from one zone to another
     * @param string $dateTime which we want to convert
     * @param string $toTz timezone in which we want to convert
     * @param string $fromTz timezone from which we want to convert
     */
    private function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }

    /**
     * Check Category Map permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::add_auction');
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
