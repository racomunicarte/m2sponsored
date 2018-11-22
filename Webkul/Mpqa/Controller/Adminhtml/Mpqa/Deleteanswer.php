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
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Deleteanswer extends \Magento\Backend\App\Action
{
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $resultPageFactory;

    protected $_answer;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @param Context       $context
     * @param PageFactory   $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        
        $this->resultPageFactory = $resultPageFactory;
        $this->_answer=$answerFactory;
        $this->_resultJsonFactory=$resultJsonFactory;
        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpqa::index');
    }
    
    public function execute()
    {
        $data=$this->getRequest()->getParams();
        $model=$this->_answer->create()->getCollection()
                ->addFieldToFilter('answer_id', $data['ans']);
        foreach ($model as $key) {
            $key->setId($key->getAnswerId())->delete();
        }
        $result = $this->_resultJsonFactory->create();
        $result->setData(['msg' => 'yes']);
        return $result;
    }
}
