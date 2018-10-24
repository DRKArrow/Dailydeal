<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_dailydeal_deal'))
            ->addColumn(
                'deal_id', Table::TYPE_INTEGER, 11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('price', Table::TYPE_DECIMAL, '10,2', ['nullable' => false, 'default' => '0.00'])
            ->addColumn('quantity', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => '0'])
            ->addColumn('sold', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => '0'])
            ->addColumn('start_time', Table::TYPE_DATETIME, null, [])
            ->addColumn('end_time', Table::TYPE_DATETIME, null, [])
            ->addColumn('progress_status', Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => ''])
            ->addColumn('status', Table::TYPE_SMALLINT, 6, ['unsigned' => true, 'nullable' => false, 'default' => '1'])
            ->setComment('Daily Deal');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_dailydeal_deal_product'))
            ->addColumn(
                'deal_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'product_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addIndex(
                $installer->getIdxName('tigren_dailydeal_deal_product', ['deal_id']), ['deal_id']
            )
            ->addIndex(
                $installer->getIdxName('tigren_dailydeal_deal_product', ['product_id']), ['product_id']
            )
            ->setComment('Deal Product');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_dailydeal_deal_store'))
            ->addColumn(
                'deal_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'store_id', Table::TYPE_SMALLINT, 6, ['unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addIndex(
                $installer->getIdxName('tigren_dailydeal_deal_store', ['deal_id']), ['deal_id']
            )
            ->addIndex(
                $installer->getIdxName('tigren_dailydeal_deal_store', ['store_id']), ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('tigren_dailydeal_deal_store', 'deal_id', 'tigren_dailydeal_deal', 'deal_id'),
                'deal_id', $installer->getTable('tigren_dailydeal_deal'), 'deal_id', Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('tigren_dailydeal_deal_store', 'store_id', 'store', 'store_id'), 'store_id',
                $installer->getTable('store'), 'store_id', Table::ACTION_CASCADE
            )
            ->setComment('Deal Store');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('tigren_dailydeal_subscriber'))
            ->addColumn(
                'subscriber_id', Table::TYPE_INTEGER, 11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('customer_name', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('status', Table::TYPE_SMALLINT, 6, ['unsigned' => true, 'nullable' => false, 'default' => '0'])
            ->addColumn('confirm_code', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->setComment('Subscriber');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
