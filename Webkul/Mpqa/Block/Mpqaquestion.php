<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpqa
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpqa\Block;

use Magento\Customer\Model\Customer;

/**
 * Webkul Mpqa Mpqaquestion Block
 */
class Mpqaquestion extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_urlinterface;

    protected $_question;

    protected $_imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_mphelper;

    /**
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\Mpqa\Model\QuestionFactory $questionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array $data
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->_mphelper = $mphelper;
        $this->_urlinterface = $context->getUrlBuilder();
        $this->_question=$questionFactory;
        $this->_objectManager = $objectManager;
        $this->_imageHelper = $context->getImageHelper();
        parent::__construct($context, $data);
        $sellerId = $this->_mphelper->getCustomerId();

        $collection = $this->_question
            ->create()->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('status', 1)
            ->setOrder('question_id', "DESC");
        $this->setCollection($collection);
    }

    /**
     * get parameters
     * @return array
     */
    public function getParams()
    {
        $data = $this->getRequest()->getParams();
        return $data;
    }

    /**
     * get questions
     * @return object
     */
    public function getQuestions()
    {
        
        return $this->getCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }
}
