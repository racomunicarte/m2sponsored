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
use Magento\Customer\Model\Session;
use Webkul\Marketplace\Model\Product;

/**
 * Webkul Mpqa Mpqaproductquestion Block
 */
class Mpqaproductquestion extends \Magento\Framework\View\Element\Template
{
     /**
      * @var \Magento\Customer\Model\Session
      */
    protected $_customerSession;

    protected $_question;

    protected $_answer;

    protected $_review;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_registry;

    protected $_request;

    protected $_mpproduct;

    protected $_date;

    protected $_questionList;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory
     * @param \Webkul\Mpqa\Model\QuestionFactory $questionFactory
     * @param \Webkul\Mpqa\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Model\ProductFactory $mpproductFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Webkul\Mpqa\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\ProductFactory $mpproductFactory,
        \Magento\Framework\Session\StorageInterface $storage,
        array $data = []
    ) {
        $this->Session= $customerSession;
        $this->_question=$questionFactory;
        $this->_answer=$answerFactory;
        $this->_review=$reviewFactory;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_request= $context->getRequest();
        $this->_mpproduct=$mpproductFactory;
        $this->_date = $date;
        $this->storage = $storage;
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
     * get questions
     * @return object
     */
    public function getQuestions()
    {
        if (!$this->_questionList) {
            $collection = $this->_question
                ->create()->getCollection()
                ->addFieldToFilter('product_id', $this->getCurrentProductId())
                ->addFieldToFilter('status', 1);
            $this->_questionList = $collection;
        }
        return $this->_questionList;
    }

    /**
     * get questions count
     * @return int
     */

    public function getQuestionsCount()
    {
        $collection = $this->getQuestions();
        return ($collection->getSize());
    }

    /**
     * get answers count
     * @return int
     */
    public function getAnswersCount()
    {
        $id=[];
        $question=$this->getQuestions();
        foreach ($question as $key) {
            $id[]=$key->getQuestionId();
        }
        $collection = $this->_answer
            ->create()->getCollection()
            ->addFieldToFilter('question_id', ["in"=>$id]);
        return ($collection->getSize());
    }

    /**
     * get seller id
     * @return int
     */
    public function getSellerId()
    {
        $collection=$this->_mpproduct->create()->getCollection()
                ->addFieldToFilter('mageproduct_id', $this->getCurrentProductId());
        if ($collection->getSize()>0) {
            foreach ($collection as $seller) {
                $id=$seller->getSellerId();
            }
            return $id;
        }
        return 0;
    }
    
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuestions()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mpqa.product.question.pager'
            )
            ->setCollection(
                $this->getQuestions()
            );
            $this->setChild('pager', $pager);
            $this->getQuestions()->load();
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

    public function getCurrentProductId()
    {
        $product = $this->_registry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * get answer of question
     * @return object
     */
    public function getQuestionAnswers($qid)
    {
        return $this->_answer->create()->getCollection()
            ->addFieldToFilter('question_id', $qid)
            ->setPageSize(1);
    }

    /**
     * get count of answer for each question
     * @return count
     */
    public function getAnswerCount($qid)
    {
        $collection = $this->_answer->create()->getCollection()
            ->addFieldToFilter('question_id', $qid);
        return $collection->getSize();
    }
    
    /**
     * return the feedback collection for the answer
     *
     * @param [int] $id
     * @return void
     */
    public function getReview($id)
    {
        return $this->_review->create()->getCollection()->addFieldToFilter('answer_id', $id);
    }

    /**
     * Retrieve customer id from current session
     *
     * @api
     * @return int|null
     */
    public function getCustomerId()
    {
        if ($this->storage->getData('customer_id')) {
            return $this->storage->getData('customer_id');
        }
        return null;
    }
}
