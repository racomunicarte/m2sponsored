<?php

namespace Magneto\Sponsored\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Update table 'marketplace_sponsored_products'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_sponsored_products'),
            'is_active',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Flag to display product'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_sponsored_products'),
            'processed_order_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Sponsorship Order'
            ]
        );
        //Add Unique Index
        $setup->getConnection()->addIndex(
            $setup->getTable('marketplace_sponsored_products'),
            $setup->getIdxName(
                'marketplace_sponsored_products',
                ['product_id', 'seller_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['product_id', 'seller_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $this->addForeignKeys($setup);
        $setup->endSetup();
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * Add foreign keys for Sponsored Products table
         * product_id, seller_id
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'marketplace_sponsored_products',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $setup->getTable('marketplace_sponsored_products'),
            'product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
