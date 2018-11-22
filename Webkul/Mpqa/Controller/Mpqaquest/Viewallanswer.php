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

/**
 * Webkul Mpqa Mpqaquest Controller.
 */
class Viewallanswer extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $_review;
    protected $_answer;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    protected $_timezone;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Webkul\Mpqa\Model\ReviewFactory $reviewFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_review=$reviewFactory;
        $this->_answer=$answerFactory;
        $this->_timezone = $timezone;
        $this->_resultJsonFactory=$resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Viewallanswer page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
         $model=$this->_answer->create()->getCollection();
         $model->addFieldToFilter('question_id', $data['quesid']);
         $answer='';
         $i=0;
        foreach ($model as $ans) {
            if ($i==0) {
                $i=1;
                continue;
            }
            $likes=0;
            $dislikes=0;
            $reviews=$this->getReview($ans->getAnswerId());
            $like='like';
            $dislike='dislike';
            foreach ($reviews as $key) {
                if ($key->getLikeDislike()==1) {
                    $likes++;
                } else {
                    $dislikes++;
                }
                if ($key->getReviewFrom()==$this->getRequest()->getParam('custid')) {
                    $like='liked';
                    $dislike='disliked';
                }
            }
            if ($ans->getRespondType()=='Admin') {
                $class='wk_admin';
            } elseif ($ans->getRespondType()=='Seller') {
                $class='wk_seller';
            } else {
                $class='wk_customer';
            }
            $answer=$answer.'<div class="extra-answ"><span class="wk_prewrap">'.$ans->getContent().'</span>
                <div class="user-info1">by <span class='.$class.'>'.$ans->getRespondNickname().'</span> on <span>'.$this->_timezone->formatDate($ans->getCreatedAt(),\IntlDateFormatter::MEDIUM).'</span><br/><div class="reviews">
                <span class="'.$like.'" title="like" dataid="'.$ans->getAnswerId().'"></span><span>'.$likes.'</span>
                <span class="'.$dislike.'" title="dislike" dataid="'.$ans->getAnswerId().'"></span><span>'.$dislikes.'</span></div></div>
            </div>';
        }
         $answer=$answer.'<div class="answe"><a class="qa-ans"><button><span>'.__('Give Answer').'</span></button></a></div>';
        $final_array=["answer"=>$answer];
        $result = $this->_resultJsonFactory->create();
        $result->setData($final_array);
        return $result;
    }

    public function getReview($id)
    {
        return $this->_review->create()->getCollection()->addFieldToFilter('answer_id', $id);
    }
}
