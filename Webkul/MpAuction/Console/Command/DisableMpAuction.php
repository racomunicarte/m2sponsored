<?php
/**
 * Webkul Software
 *
 * @category Magento
 * @package  Webkul_MpAuction
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableMpAuction
 */
class DisableMpAuction extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $_modStatus;


    /**
     * @param \Magento\Eav\Setup\EavSetupFactory        $eavSetupFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager         $moduleManager
     * @param \Magento\Framework\Module\Status          $modStatus
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus
    ) {
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpauction:disable')
            ->setDescription('MpAuction Disable Command');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->_moduleManager->isEnabled('Webkul_MpAuction')) {
            $connection = $this->_resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            //drop foreign key
            $connection->dropForeignKey(
                $connection->getTableName('wk_mpauction_amount'),
                $connection->getForeignKeyName(
                    'wk_mpauction_amount',
                    'auction_id',
                    'wk_mpauction_product',
                    'entity_id'
                )
            );
            $connection->dropForeignKey(
                $connection->getTableName('wk_mpauto_auction'),
                $connection->getForeignKeyName(
                    'wk_mpauto_auction',
                    'auction_id',
                    'wk_mpauction_product',
                    'entity_id'
                )
            );
            $connection->dropForeignKey(
                $connection->getTableName('wk_mpauction_winner_data'),
                $connection->getForeignKeyName(
                    'wk_mpauction_winner_data',
                    'auction_id',
                    'wk_mpauction_product',
                    'entity_id'
                )
            );

            // drop custom tables
            $connection->dropTable($connection->getTableName('wk_mpauction_product'));
            $connection->dropTable($connection->getTableName('wk_mpauction_amount'));
            $connection->dropTable($connection->getTableName('wk_mpauction_incremental_price'));
            $connection->dropTable($connection->getTableName('wk_mpauto_auction'));
            $connection->dropTable($connection->getTableName('wk_mpauction_winner_data'));
            
            // delete auction_type product attribute
            $this->_eavAttribute->loadByCode('catalog_product', 'auction_type')->delete();

            // disable mpauction module
            $this->_modStatus->setIsEnabled(false, ['Webkul_MpAuction']);

            // delete entry from setup_module table
            $tableName = $connection->getTableName('setup_module');
            $connection->query("DELETE FROM " . $tableName . " WHERE module = 'Webkul_MpAuction'");
            $output->writeln('<info>Marketplace Seller Auction has been disabled successfully.</info>');
        }
    }
}
