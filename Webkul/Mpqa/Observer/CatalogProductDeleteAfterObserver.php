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

namespace Webkul\Mpqa\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Mpqa\Api\QuestionAnswerRepositoryInterface;

/**
 * Webkul Marketplace CatalogProductDeleteAfterObserver Observer.
 */
class CatalogProductDeleteAfterObserver implements ObserverInterface
{
   
    protected $_questionFactory;

    protected $_answerFactory;
    
    protected $_qaRepository;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        \Webkul\Mpqa\Model\QuestionFactory $questionFactory,
        \Webkul\Mpqa\Model\MpqaanswerFactory $answerFactory,
        QuestionAnswerRepositoryInterface $qaReporitory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_questionFactory = $questionFactory;
        $this->_answerFactory = $answerFactory;
        $this->_date = $date;
        $this->_qaRepository = $qaReporitory;
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $productId = $observer->getProduct()->getId();
            $this->_qaRepository->deleteById($productId);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
