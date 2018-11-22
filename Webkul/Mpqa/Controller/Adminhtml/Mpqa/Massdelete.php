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
namespace Webkul\Mpqa\Controller\Adminhtml\Mpqa;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Ui\Component\MassAction\Filter;

class Massdelete extends \Magento\Backend\App\Action
{
    protected $_question;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        Action\Context $context,
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory
    ) {
        $this->_filter = $filter;
        $this->_question = $questionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $questionCollection = $this->_question->create();
            $collection = $this->_filter->getCollection($questionCollection->getCollection());
            foreach ($collection as $ques) {
                $ques->setId($ques->getQuestionId())
                    ->delete();
            }
            $this->messageManager->addSuccess(__('Question(s) deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
