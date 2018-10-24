<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block;

class Dailydeal extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getIdentities()
    {
        return [\Tigren\Dailydeal\Model\Deal::CACHE_TAG . '_' . 'dailydeal'];
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

}
