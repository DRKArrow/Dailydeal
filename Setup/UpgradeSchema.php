<?php namespace Tigren\Dailydeal\Setup;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(version_compare($context->getVersion(), '1.0.3') < 0) {
            $tableName = $installer->getTable('tigren_dailydeal_deal_product');
            $columns = [
                'deal_product_qty' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Deal Product Quantity'
                ],
                'deal_product_sold' => [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Deal Product Sold'
                ]
            ];

            $connection = $installer->getConnection();

            foreach($columns as $name => $column){
                $connection->addColumn($tableName, $name, $column);
            }

        }

        $installer->endSetup();
    }
}