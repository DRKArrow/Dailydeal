<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Deal\Edit\Tab;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
    protected $_dealFactory;
    protected $_linkFactory;
    protected $_productStatus;
    protected $_productVisibility;
    protected $_productType;
    protected $_setsFactory;
    protected $_productFactory;
    protected $_websiteFactory;
    protected $moduleManager;
    protected $_dailydealHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_dealFactory = $dealFactory;
        $this->_linkFactory = $linkFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->_productType = $productType;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        $this->_dailydealHelper = $dailydealHelper;
        parent::__construct($context, $backendHelper, $data);

    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productgrid',
            ['_current' => true]);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('deal_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_linkFactory->create()->getProductCollection()
            ->addAttributeToSelect('*');
        $dealId = $this->getRequest()->getParam('deal_id');
        $dealThis = $this->_dealFactory->create()->load($dealId);

        $associatedProductIds = [];
        $deals = $this->_dealFactory->create()->getCollection();
        foreach ($deals->getItems() as $deal) {
            if($dealThis->getId() == $deal->getId()) {
                continue;
            }
            $deal->load($deal->getId());
            $dealProductIds = $deal->getProductIds();
            foreach ($dealProductIds as $id) {
                $associatedProductIds[] = $id;
            }
        }

        if ($associatedProductIds) {
            $collection->addFieldToFilter('entity_id', ['nin' => $associatedProductIds]);
        }

        $excludedPrdType = ['bundle', 'grouped'];
        $collection->addAttributeToFilter('visibility', ['nin' => [1]]);
        $collection->addAttributeToFilter('type_id', ['nin' => $excludedPrdType]);
        $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
        $collection->addAttributeToFilter('price', ['gt' => 0]);
        if ($this->getScopeConfig('dailydeal/general/apply_is_deal')) {
            $collection->addAttributeToFilter('is_deal', 1);
        }


        //Add Quantity of Product
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'inner'
            );
        }

        $collection->addWebsiteNamesToResult();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getScopeConfig($path)
    {
        $storeId = $this->getCurrentStoreId();
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProductIds();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Retrieve selected items key
     *
     * @return array
     */
    protected function _getSelectedProductIds()
    {
        $products = array_keys($this->getSelectedProductIds());
        return $products;
    }

    /**
     * Retrieve selected items key
     *
     * @return array
     */
    public function getSelectedProductIds()
    {
        $dealId = $this->getRequest()->getParam('deal_id');
        $deal = $this->_dealFactory->create()->load($dealId);
        $productIds = $deal->getProductIds();

        if (!$productIds) {
            return [];
        }

        $productIdArr = [];

        foreach ($productIds as $productId) {
            $productIdArr[$productId] = ['id' => $productId];
        }

        return $productIdArr;
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $dealId = $this->getRequest()->getParam('deal_id');
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProductIds(),
                'align' => 'center',
                'index' => 'entity_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn(
            'product_type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_productType->getOptionArray(),
            ]
        );

        $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();
        $this->addColumn(
            'product_set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ]
        );
        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );
        $this->addColumn(
            'product_price',
            [
                'header' => __('Price'),
                'type' => 'number',
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'product_currency',
            [
                'header' => __('Currency'),
                'default' => (string)$this->_scopeConfig->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) . ' (' . $this->_dailydealHelper->getBaseCurrencySymbol() . ')'
            ]
        );
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn(
                'product_qty',
                [
                    'header' => __('Qty in Stock'),
                    'type' => 'number',
                    'index' => 'qty'
                ]
            );
        }
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'product_websites',
                [
                    'header' => __('Websites'),
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                ]
            );
        }
        $this->addColumn(
            'product_visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_productVisibility->getOptionArray(),
            ]
        );
        $this->addColumn(
            'product_status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_productStatus->getOptionArray(),
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => true,
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position'
            ]
        );

        return parent::_prepareColumns();
    }
}