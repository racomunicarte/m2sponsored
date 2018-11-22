<?php
/**
 * Webkul_MpAuction Admin Config Incremental Price Save Controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpAuction\Model\IncrementalPrice;

class IncrementalPriceSave extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Webkul\MpAuction\Model\IncrementalPrice
     */
    private $incrementPrice;

    /**
     * ValidateTest constructor.
     *
     * @param Action\Context  $context
     * @param JsonFactory     $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        IncrementalPrice $incrementPrice
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->incrementPrice = $incrementPrice;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data && isset($data['increment'])) {
            $increment = [];
            foreach ($data['increment']['from'] as $key => $value) {
                if ($data['increment']['from'][$key] > $data['increment']['to'][$key]) {
                    $temp = $data['increment']['from'][$key];
                    $data['increment']['from'][$key] = $data['increment']['to'][$key];
                    $data['increment']['to'][$key] = $temp;
                }
                if ($data['increment']['price'][$key] < 0 || $data['increment']['price'][$key] == '') {
                    $data['increment']['price'][$key] = 1;
                }
                $indexKey = $data['increment']['from'][$key].'-'.$data['increment']['to'][$key];
                $increment[$indexKey] = $data['increment']['price'][$key];
            }
            $incrementPriceDetail = $this->incrementPrice->getCollection()->setPageSize(1)->getFirstItem();
            $incrementPriceDetail->setIncval(json_encode($increment));
            $incrementPriceDetail->save();
            $result = ['error' => 0, 'msg' => __('Incremental price saved successfully')];
        } else {
            $result = ['error' => 1,'msg' => __('Invalid data')];
        }
        return $resultJson->setData($result);
    }

    /**
     * Check Category Map permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAuction::set_inc_price_range');
    }
}
