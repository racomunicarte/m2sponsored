<?php
namespace Magneto\Blind\Helper;

class Blind extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Blind constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function getBlindOptionArray()
    {
        return $this->_objectManager->create(
            'Magneto\Blind\Model\Product\Source\YesNo'
        )->getOptionArray();
    }
}

?>