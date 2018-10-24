<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_deal',
            [
                'type' => 'int',
                'label' => 'Is Deal',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'default' => '1',
                'sort_order' => 110,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'General',
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true
            ]
        );

        $isDealAttribute = $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_deal');
        $this->updateProductAttributes($setup, $isDealAttribute);
    }

    public function updateProductAttributes(ModuleDataSetupInterface $setup, $isDealAttribute)
    {
        if (empty($isDealAttribute['attribute_id'])) {
            return false;
        }

        $connection = $setup->getConnection();

        $productEntityTable = $connection->getTableName('catalog_product_entity');
        $productEntityIntTable = $connection->getTableName('catalog_product_entity_int');
        $columns = [
            'attribute_id' => new \Zend_Db_Expr($isDealAttribute['attribute_id']),
            'entity_id' => 'entity_id',
            'value' => new \Zend_Db_Expr(1)
        ];

        $select = $connection->select()->from(
            [$productEntityTable],
            $columns
        );
        $query = $select->insertFromSelect($productEntityIntTable, array_keys($columns));

        $connection->query($query);
    }
}