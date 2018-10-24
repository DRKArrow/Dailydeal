<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Mysql resource
 */
class Deal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_dealTable;
    protected $_dealStoreTable;
    protected $_dealProductTable;
    protected $_productFactory;
    protected $_dealFactory;
    protected $_date;
    protected $_storeManager;
    protected $_dailydealHelper;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ProductFactory $prductFactory,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_productFactory = $prductFactory;
        $this->_dealFactory = $dealFactory;
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_dailydealHelper = $dailydealHelper;
    }

    public function deleteAssociations($productId)
    {
        if ($productId) {
            $condition = ['product_id = ?' => $productId];
            $connection = $this->getConnection();
            $connection->delete($this->_dealProductTable, $condition);
        }
    }

    public function getDealByProductId($productId)
    {
        $storeIds = ['0', $this->getCurrentStoreId()];

        $select = $this->getConnection()->quoteInto("
            SELECT d.`deal_id`
            FROM $this->_dealTable as d
            INNER JOIN $this->_dealStoreTable as s
                ON s.`deal_id` = d.`deal_id`
            INNER JOIN $this->_dealProductTable as dp
                ON d.`deal_id` = dp.`deal_id`
                AND s.`store_id` IN (?)
                AND dp.product_id = $productId
            GROUP BY d.`deal_id`
        ", $storeIds);
        return $this->getConnection()->fetchOne($select);
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    public function getTodayDealsEndTime()
    {
        $productTable = $this->getTable('catalog_product_entity');
        $storeIds = [0, $this->getCurrentStoreId()];

        $select = $this->getConnection()->quoteInto("
            SELECT dp.`product_id`, d.`end_time`
            FROM $this->_dealTable as d
            INNER JOIN $this->_dealStoreTable as s
                ON s.`deal_id` = d.`deal_id`
            INNER JOIN $this->_dealProductTable as dp
                ON d.`deal_id` = dp.`deal_id`
            INNER JOIN $productTable as p
                ON dp.`product_id` = p.`entity_id`
            WHERE (d.`start_time` < now() AND d.`end_time` > now())
                AND d.`status` = " . \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED . "
                AND (d.`quantity` - d.`sold`) > 0
                AND s.`store_id` IN (?)
            GROUP BY dp.`product_id`
            ORDER BY d.`price` ASC
        ", $storeIds);

        return $this->getConnection()->fetchAll($select);
    }

    protected function _construct()
    {
        $this->_init('tigren_dailydeal_deal', 'deal_id');
        $this->_dealTable = $this->getTable('tigren_dailydeal_deal');
        $this->_dealStoreTable = $this->getTable('tigren_dailydeal_deal_store');
        $this->_dealProductTable = $this->getTable('tigren_dailydeal_deal_product');

    }

    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        if (!$object->getId()) {   //if create new
            return $this;
        }

        //Process time
        if ($object->hasStartTime()) {
            $startTime = date('Y-m-d H:i:s',
                $this->_date->timestamp($object->getStartTime()) + $this->_date->getGmtOffset());
            $object->setStartTime($startTime);
        }
        if ($object->hasEndTime()) {
            $endTime = date('Y-m-d H:i:s',
                $this->_date->timestamp($object->getEndTime()) + $this->_date->getGmtOffset());
            $object->setEndTime($endTime);
        }

        if ($object->getId()) {
            $object->setStores($this->getStoreIds((int)$object->getId()));
            $object->setProductIds($this->getProductIds((int)$object->getId()));
        }
        return $this;
    }

    public function getStoreIds($dealId)
    {
        $select = $this->getConnection()->select()->from(
            $this->_dealStoreTable, 'store_id')
            ->where('deal_id = ?', $dealId);
        return $this->getConnection()->fetchCol($select);
    }

    public function getProductIds($dealId)
    {
        $select = $this->getConnection()->select()->from(
            $this->_dealProductTable, 'product_id')
            ->where('deal_id = ?', $dealId);
        return $this->getConnection()->fetchCol($select);
    }

    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores([$object->getStores(), 0]);
        }
        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        //Add title while importing
        if (empty($object->getTitle())) {
            $title = 'Deal' . $object->getId();
            $this->_dealFactory->create()->load($object->getId())->setTitle($title)->save();
        }

        //Save Deal Stores
        $condition = ['deal_id = ?' => $object->getId()];
        $connection->delete($this->_dealStoreTable, $condition);
        $stores = $object->getStores();

        if (!empty($stores)) {
            $insertedStoreIds = [];
            $fullStoreIds = $this->getAllStoreIds();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds) || !in_array((int)$storeId, $fullStoreIds)) {
                    continue;
                }
                $insertedStoreIds[] = $storeId;
                $storeInsert = ['store_id' => $storeId, 'deal_id' => $object->getId()];
                $connection->insert($this->_dealStoreTable, $storeInsert);
            }
        }

        //Save Deal Products
        $productIds = $object->getProductIds();
        if (!empty($productIds)) { //When create new and product is selected // Change logic to update deal can add, remove product
            $products = [];
            foreach($productIds as $productId) {
                $products[] = ['product_id' => $productId, 'sold_qty' => $this->_dailydealHelper->getSoldQuantity($productId)];
            }

            $condition = ['deal_id = ?' => $object->getId()];
            $connection->delete($this->_dealProductTable, $condition);

            $insertedProductIds = [];
            foreach ($products as $product) {
                if (in_array($product['product_id'], $insertedProductIds)) {
                    continue;
                }
                $insertedProductIds[] = $product['product_id'];

                $insert = ['product_id' => $product['product_id'],
                    'deal_id' => $object->getId(),
                    'deal_product_qty' => $object->getQuantity(),
                    'deal_product_sold' => $product['sold_qty']];
                $connection->insert($this->_dealProductTable, $insert);
            }
        }

        //Save Special Price for Product
        $deal = $this->_dealFactory->create()->load($object->getId());
        if ($deal->isNotEnded()) {
            $localStartTime = date('Y-m-d H:i:s',
                $this->_date->timestamp($object->getStartTime()) + $this->_date->getGmtOffset());
            $localEndTime = date('Y-m-d H:i:s',
                $this->_date->timestamp($object->getEndTime()) + $this->_date->getGmtOffset());
            foreach ($productIds as $id) {
                $product = $this->_productFactory->create()->load($id);
                $product->setSpecialPrice($object->getPrice());
                $product->setSpecialFromDate($localStartTime);
                $product->setSpecialFromDateIsFormated(true);
                $product->setSpecialToDate($localEndTime);
                $product->setSpecialToDateIsFormated(true);
                $product->setStoreId(0);
                $product->save();
            }
        } else {
            foreach ($productIds as $id) {
                $product = $this->_productFactory->create()->load($id);
                if ($product->getId()) {
                    $product->setSpecialPrice(null);
                    $product->setSpecialFromDate(null);
                    $product->setSpecialFromDateIsFormated(true);
                    $product->setSpecialToDate(null);
                    $product->setSpecialToDateIsFormated(true);
                    $product->setStoreId(0);
                    $product->save();
                }
            }
        }

        //Refresh cache for deals
        $this->_dailydealHelper->refreshLocalDeals();
        return $this;
    }

    public function getAllStoreIds()
    {
        $stores = $this->_storeManager->getStores();
        $ids = array_keys($stores);
        array_unshift($ids, 0);
        return $ids;
    }

    protected function _afterDelete(AbstractModel $object)
    {
        //Save Special Price for Product
        $productIds = $this->getProductIds((int)$object->getId());
        foreach ($productIds as $id) {
            $product = $this->_productFactory->create()->load($id);
            if ($product->getId()) {
                $product->setSpecialPrice(null);
                $product->setSpecialFromDate(null);
                $product->setSpecialFromDateIsFormated(true);
                $product->setSpecialToDate(null);
                $product->setSpecialToDateIsFormated(true);
                $product->setStoreId(0);
                $product->save();
            }
        }

        //Delete product_deal association
        $condition = ['deal_id = ?' => $object->getId()];
        $connection = $this->getConnection();
        $connection->delete($this->_dealProductTable, $condition);

        //Refresh cache for deals
        $this->_dailydealHelper->refreshLocalDeals();
        return parent::_afterDelete($object);
    }


}
