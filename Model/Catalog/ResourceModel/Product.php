<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\Catalog\ResourceModel;

/**
 * Mysql resource
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    protected $_dealFactory;

    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $catalogCategory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        $data = []
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $categoryCollectionFactory, $catalogCategory,
            $eventManager, $setFactory, $typeFactory, $defaultAttributes, $data);
        $this->_dealFactory = $dealFactory;
    }

    protected function _afterDelete(\Magento\Framework\DataObject $object)
    {
        $productId = $object->getId();
        $deal = $this->_dealFactory->create()->loadByProductId($productId);
        if ($deal->getId() && count($deal->getProductIds()) == 1) {
            $deal->delete();
        } else {
            $deal->getResource()->deleteAssociations($productId);
        }
        return parent::_afterDelete($object);
    }

}
