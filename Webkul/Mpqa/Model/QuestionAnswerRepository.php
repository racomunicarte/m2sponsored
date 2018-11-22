<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ProductQuestionAnswer
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpqa\Model;
 
class QuestionAnswerRepository implements \Webkul\Mpqa\Api\QuestionAnswerRepositoryInterface
{   
    protected $_questionFactory;

    protected $_answerFactory;

    public function __construct(
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory
    ) {
        $this->_answerFactory = $answerFactory;
        $this->_questionFactory = $questionFactory;
    }
    /**
     * Delete question by product ID.
     */
    public function deleteById($productId)
    {
        $question_ids = [];
        $question_ids = $this->_questionFactory->create()->getCollection()
                        ->addFieldToFilter('product_id', $productId)
                        ->getAllIds();

        foreach ($question_ids as $id) {
            $this->_questionFactory->create()->load($id)->setId($id)->delete();
        }
    }
}