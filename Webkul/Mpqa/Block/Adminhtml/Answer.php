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
namespace Webkul\Mpqa\Block\Adminhtml;

use Magento\Catalog\Helper\Image;

class Answer extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    protected $_question;

    protected $_productloader;

    protected $_answer;

    protected $_imagehelper;
    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Customer\Model\Customer        $customer
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_formFactory = $formFactory;
        $this->_customer = $customer;
        $this->_question=$questionFactory;
        $this->_answer=$answerFactory;
        $this->_imagehelper = $imageHelper;
        $this->_productloader=$_productloader;
        parent::__construct($context, $data);
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
     * get Question
     * @return object
     */
    public function getQuestion()
    {
        $data = $this->getParams();
        if ($data['id']) {
            $collection = $this->_question
                ->create()->load($data['id']);
                    
            return $collection;
        }
    }
    public function getProduct()
    {
        
        if ($this->getQuestion()->getProductId()) {
            return $this->_productloader->create()->load($this->getQuestion()->getProductId());
        }
    }
    public function getAnswers()
    {
        $collection = $this->_answer->create()->getCollection()
                    ->addFieldToFilter('question_id', $this->getRequest()->getParam('id'))
                    ->setOrder('answer_id', "DESC");
        return $collection;
    }
    public function imageHelperObj()
    {
        return $this->_imagehelper;
    }
}
