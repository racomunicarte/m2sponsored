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
 * Webkul Mpqa Viewall Block
 */
class Viewall extends \Magento\Framework\View\Element\Template
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

    protected $_product;

    protected $_imagehelper;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory
     * @param \Webkul\Mpqa\Model\QuestionFactory $questionFactory
     * @param \Webkul\Mpqa\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Model\ProductFactory $mpproductFactory
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Webkul\Mpqa\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\ProductFactory $mpproductFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\ResourceConnection $resource,
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
        $this->_product=$_productloader;
        $this->_imagehelper=$imageHelper;
        $this->_resource=$resource;
        parent::__construct($context, $data);

        $collection = $this->_question
            ->create()->getCollection()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('status', 1);
        $data=$this->getParams();
        if (isset($data['q'])) {
            $collection->addFieldToFilter(
                ['content','subject'],
                [
                    ['like'=>'%'.$data['q'].'%'],
                    ['like'=>'%'.$data['q'].'%']
                ]
            );
        }
        if (isset($data['helpful'])) {
            $q_id=[];
            foreach ($collection as $key) {
                $q_id[]=$key->getQuestionId();
            }
            $answer=$this->_answer->create()->getCollection()
                        ->addFieldToFilter('question_id', ['in'=>$q_id]);
            $answer->getSelect()
            ->join(
                ['qa_rev' => $this->_resource->getTableName('mp_qarespondreview')],
                "main_table.answer_id = qa_rev.answer_id",
                ["count"=>"COUNT(qa_rev.like_dislike)",
                 "question_id"=>"main_table.question_id"
                ]
            )
            ->group('main_table.question_id')
            ->where("qa_rev.like_dislike = 1");

            $answer->setOrder('count', 'DESC');
            $q_id=[];
            foreach ($answer as $key) {
                $q_id[]=$key->getQuestionId();
            }
           
            $collection=$this->_question->create()->getCollection();
            $collection->addFieldToFilter('question_id', ['in'=>$q_id]);
        }
        if (isset($data['recent'])) {
            $collection->setOrder('question_id', 'DESC');
        }
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
        
        $data = $this->getParams();
        $collection = $this->_question
            ->create()->getCollection()
            ->addFieldToFilter('product_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('status', 1);
        ;
        return $collection;
    }

    public function getQuestionsCount()
    {
        $collection = $this->_question
            ->create()->getCollection()
            ->addFieldToFilter('product_id', $this->getCurrentProduct()->getId())
            ->addFieldToFilter('status', 1);
        return ($collection->getSize());
    }

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
    public function getSellerId()
    {
        $collection=$this->_mpproduct->create()->getCollection()
                ->addFieldToFilter('mageproduct_id', $this->getRequest()->getParam('id'));
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
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mpqa.mpqa.list.pager'
            )->setAvailableLimit([10=>10,20=>20,30=>30])->setShowPerPage(true)
            ->setCollection(
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

    public function getCurrentProduct()
    {
        return $this->_product->create()->load($this->getRequest()->getParam('id'));
    }

    public function getQuestionAnswers($qid)
    {
        return $this->_answer->create()->getCollection()
            ->addFieldToFilter('question_id', $qid)
            ->setPageSize(1);
    }

    public function getReview($id)
    {
        return $this->_review->create()->getCollection()->addFieldToFilter('answer_id', $id);
    }

    public function imageHelperObj()
    {
        return $this->_imagehelper;
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
}
