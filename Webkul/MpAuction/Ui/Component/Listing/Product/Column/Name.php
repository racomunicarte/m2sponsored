<?php
/**
 * Webkul MpAuction Product Name UI.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Ui\Component\Listing\Product\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Catalog\Model\Product;

/**
 * Class Status.
 */
class Name extends Column
{
    /**
     * @var Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * Constructor.
     *
     * @param ContextInterface           $context
     * @param UiComponentFactory         $uiComponentFactory
     * @param ProductRepositoryInterface $productRepository
     * @param array                      $components
     * @param array                      $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Product $product,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->product = $product;
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $product = $this->getProduct($item['product_id']);
                $item[$this->getData('name')] = $item['product_id']? $product->getName() : __('Not Available');
            }
        }
        return $dataSource;
    }

    /**
     * getProduct
     * @param int $productId
     * @return Magento\Catalog\Model\Product
     */
    private function getProduct($productId)
    {
        return $this->product->load($productId);
    }
}
