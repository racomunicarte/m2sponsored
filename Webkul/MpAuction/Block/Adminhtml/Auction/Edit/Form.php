<?php
/**
 * Webkul_MpAuction Add New Auction Form Admin Block.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Block\Adminhtml\Auction\Edit;

use Webkul\MpAuction\Model\ResourceModel\Product\Source\AllProductsForAuction;

/**
 * Adminhtml Add New MpAuction Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var AllProductsForAuction
     */
    private $allProducts;

    /**
     * @var array configuration of MpAuction
     */

    private $auctionConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Webkul\MpAuction\Helper\Data             $auctionHelperData
     * @param AllProductsForAuction                   $allProducts
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\MpAuction\Helper\Data $auctionHelperData,
        AllProductsForAuction $allProducts,
        array $data = []
    ) {
        $this->allProducts = $allProducts;
        $this->auctionConfig = $auctionHelperData->getAuctionConfiguration();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('auction_product');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('wkauction_');
        if ($model->getEntityId()) {
            $model->setStartAuctionTime(date_format(date_create($model->getStartAuctionTime()), 'm/d/y H:i:s'));
            $model->setStopAuctionTime(date_format(date_create($model->getStopAuctionTime()), 'm/d/y H:i:s'));
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Auction'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add New Auction'), 'class' => 'fieldset-wide']
            );
        }
        if ($model->getEntityId()) {
            $data = $this->allProducts->productListForAuction($model->getProductId());
            foreach ($data as $pro) {
                if ($pro['value'] == $model->getProductId()) {
                    $model->setProductName($pro['label']);
                    $model->setProductId($pro['value']);
                }
            }
            $fieldset->addField(
                'product_name',
                'text',
                [
                    'name' => 'product_name',
                    'label' => __('Product Name'),
                    'id' => 'product_id',
                    'title' => __('Product Name'),
                    'class' => 'required-entry',
                    'required' => true,
                    'readonly' => true
                ]
            );
            $fieldset->addField(
                'product_id',
                'hidden',
                [
                    'name' => 'product_id'
                ]
            );
        } else {
            $fieldset->addField(
                'product_id',
                'select',
                [
                    'name' => 'product_id',
                    'label' => __('Product Name'),
                    'id' => 'product_id',
                    'title' => __('Product Name'),
                    'values' => $this->allProducts->productListForAuction($model->getProductId()),
                    'class' => 'required-entry',
                    'required' => true,
                ]
            );
        }
        $fieldset->addField(
            'starting_price',
            'text',
            [
                'name' => 'starting_price',
                'label' => __('Starting Price'),
                'id' => 'starting_price',
                'title' => __('Starting Price'),
                'type' => 'price',
                'class' => 'required-entry validate-zero-or-greater validate-number',
                'required' => true,
            ]
        );

        $reqClass = "";
        $requiredOpt = false;
        if ($this->auctionConfig['reserve_enable']) {
            $reqClass = 'required-entry';
            $requiredOpt = true;
        }
        $fieldset->addField(
            'reserve_price',
            'text',
            [
                'name' => 'reserve_price',
                'label' => __('Reserve Price'),
                'id' => 'reserve_price',
                'title' => __('Reserve Price'),
                'values' => [],
                'class' => $reqClass.' validate-zero-or-greater validate-number',
                'required' => $requiredOpt,
            ]
        );
        
        $fieldset->addField(
            'start_auction_time',
            'date',
            [
                'name' => 'start_auction_time',
                'label' => __('Start Auction Time'),
                'id' => 'start_auction_time',
                'title' => __('Start Auction Time'),
                'date_format' => $dateFormat,
                'time_format' => 'HH:mm:ss',
                'class' => 'required-entry admin__control-text',
                'style' => 'width:210px',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'stop_auction_time',
            'date',
            [
                'name' => 'stop_auction_time',
                'label' => __('Stop Auction Time'),
                'id' => 'stop_auction_time',
                'title' => __('Stop Auction Time'),
                'date_format' => $dateFormat,
                'time_format' => 'HH:mm:ss',
                'class' => 'required-entry admin__control-text',
                'style' => 'width:210px',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'days',
            'text',
            [
                'name' => 'days',
                'label' => __('Number of Days Till Winner Can Buy'),
                'id' => 'days',
                'title' => __('Number of Days Till Winner Can Buy'),
                'class' => 'required-entry integer validate-greater-than-zero validate-number',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'min_qty',
            'text',
            [
                'name' => 'min_qty',
                'label' => __('Minimum Quantity'),
                'id' => 'min_qty',
                'title' => __('Minimum Quantity'),
                'class' => 'required-entry validate-zero-or-greater validate-number',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'max_qty',
            'text',
            [
                'name' => 'max_qty',
                'label' => __('Maximum Quantity'),
                'id' => 'max_qty',
                'title' => __('Maximum Quantity'),
                'class' => 'required-entry validate-zero-or-greater validate-number',
                'required' => true,
            ]
        );

        if ($this->auctionConfig['increment_auc_enable']) {
            $fieldset->addField(
                'increment_opt',
                'select',
                [
                    'name' => 'increment_opt',
                    'label' => __('Increment Option'),
                    'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
                    'id' => 'increment_opt',
                    'title' => __('Increment Option'),
                    'class' => 'required-entry',
                    'required' => true,
                ]
            );
        }

        if ($this->auctionConfig['auto_enable']) {
            $fieldset->addField(
                'auto_auction_opt',
                'select',
                [
                    'name' => 'auto_auction_opt',
                    'label' => __('Automatic Option'),
                    'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
                    'id' => 'auto_auction_opt',
                    'title' => __('Automatic Option'),
                    'class' => 'required-entry',
                    'required' => true,
                ]
            );
        }

        $form->setValues($model->getData());
        if ($this->_isReAuction()) {
            $form->setValues(['product_id' => $this->_isReAuction()]);
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * check auction for reorder
     * @return int | bool
     */

    protected function _isReAuction()
    {
        $proId = $this->getRequest()->getParam('pro_id');
        return $proId ? $proId : false;
    }
}
