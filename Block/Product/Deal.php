<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Product;

class Deal extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_dealFactory;
    protected $_dailydealHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_dealFactory = $dealFactory;
        $this->_dailydealHelper = $dailydealHelper;
    }

    public function getIdentities()
    {
        return [\Tigren\Dailydeal\Model\Deal::CACHE_TAG . '_' . 'product_deal'];
    }

    public function getHelper()
    {
        return $this->_dailydealHelper;
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

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getDealForProduct($productId)
    {
        return $this->_dealFactory->create()->loadByProductId($productId);
    }

}
