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
namespace Webkul\Mpqa\Controller\Mpqaquest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Webkul MpSellerCoupons Mpqaquest Controller.
 */
class Mosthelpful extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $_review;
    protected $_answer;
    protected $_question;
    protected $_timezone;
    protected $_mpHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Mpqa\Model\ReviewFactory $reviewFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Timezone $timezone,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_review=$reviewFactory;
        $this->_answer=$answerFactory;
        $this->_resource=$resource;
        $this->_question=$questionFactory;
        $this->_resultJsonFactory=$resultJsonFactory;
        $this->_timezone = $timezone;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $pid = $this->getRequest()->getParam('pid');
        $questionCollection=$this->_question->create()->getCollection();
        $questionCollection->addFieldToFilter('product_id', $pid);
        $questionCollection->addFieldToFilter('status', 1);
        $customerId = $this->_mpHelper->getCustomerId();
        $q_id=[];
        foreach ($questionCollection as $key) {
            $q_id[]=$key->getQuestionId();
        }
        $collection=$this->_answer->create()->getCollection()
                    ->addFieldToFilter('question_id', ['in'=>$q_id]);
        $collection->getSelect()
        ->join(
            ['qa_rev' => $this->_resource->getTableName('mp_qarespondreview')],
            "main_table.answer_id = qa_rev.answer_id",
            ["count"=>"COUNT(qa_rev.like_dislike)",
             "question_id"=>"main_table.question_id"
            ]
        )
        ->group('main_table.question_id')
        ->where("qa_rev.like_dislike = 1");

        $collection->setOrder('count', 'DESC');
        $q_id=[];
        foreach ($collection as $key) {
            $q_id[]=$key->getQuestionId();
        }
        $response=[];
        $final_array=[];
        $questions=$this->_question->create()->getCollection();
        $questions->addFieldToFilter('question_id', ['in'=>$q_id]);
        foreach ($questions as $key) {
            $answers=$this->getQuestionAnswers($key->getQuestionId());
            if (count($answers)>0) {
                foreach ($answers as $ans) {
                    $likes=0;
                    $dislikes=0;
                    $reviews=$this->getReview($ans->getAnswerId());
                    $rating = 0;
                    foreach ($reviews as $rev) {
                        if ($rev->getLikeDislike()==1) {
                            $likes++;
                        } else {
                            $dislikes++;
                        }
                        if ($rev->getReviewFrom() == $customerId) {
                            $rating = 1;
                        }
                    }
                    $response['rating'] = $rating;
                    $response['likes']=$likes;
                    $response['dislikes']=$dislikes;
                    $response['answer_id']=$ans->getAnswerId();
                    $response['answer']=$ans->getContent();
                    $response['nickname']=$ans->getRespondNickname();
                    $response['createdat']= $this->_timezone->formatDate($ans->getCreatedAt(),\IntlDateFormatter::MEDIUM);
                }
            }
            $answer_count = $this->getAnswerCount($key->getQuestionId());
            $response['count'] = $answer_count;
            $response['qa_nickname']=$key->getQaNickname();
            $response['qa_date']= $this->_timezone->formatDate($key->getCreatedAt(),\IntlDateFormatter::MEDIUM);
            $response['question_id']=$key->getQuestionId();
            $response['content']=$key->getContent();
            $response['subject']=$key->getSubject();
            array_push($final_array, $response);
        }
        $result = $this->_resultJsonFactory->create();
        $result->setData($final_array);
        return $result;
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
