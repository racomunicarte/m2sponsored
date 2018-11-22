<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Setup;

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
        $setup->getConnection()->changeColumn(
            $setup->getTable('wk_mpauction_winner_data'),
            'auction_id',
            'auction_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => '10',
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'auction id'
            ]
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
         * Add foreign keys for Auction ID
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_mpauction_amount',
                'auction_id',
                'wk_mpauction_product',
                'entity_id'
            ),
            $setup->getTable('wk_mpauction_amount'),
            'auction_id',
            $setup->getTable('wk_mpauction_product'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        
        /**
         * Add foreign keys for Auction ID
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_mpauto_auction',
                'auction_id',
                'wk_mpauction_product',
                'entity_id'
            ),
            $setup->getTable('wk_mpauto_auction'),
            'auction_id',
            $setup->getTable('wk_mpauction_product'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        /**
         * Add foreign keys for Auction ID
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_mpauction_winner_data',
                'auction_id',
                'wk_mpauction_product',
                'entity_id'
            ),
            $setup->getTable('wk_mpauction_winner_data'),
            'auction_id',
            $setup->getTable('wk_mpauction_product'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
