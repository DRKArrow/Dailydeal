<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block;

class Sidebar extends \Magento\Catalog\Block\Product\AbstractProduct
{
    protected $_dealFactory;
    protected $_dailydealHelper;
    protected $urlHelper;
    protected $_formKey;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dealFactory = $dealFactory;
        $this->_dailydealHelper = $dailydealHelper;
        $this->urlHelper = $urlHelper;
        $this->_formKey = $formKey;
    }

    public function getIdentities()
    {
        return [\Tigren\Dailydeal\Model\Deal::CACHE_TAG . '_' . 'sidebar'];
    }

    public function getLimit()
    {
        $limit = (int)$this->getScopeConfig('dailydeal/general/num_of_sidebar_deals');
        if (empty($limit)) {
            $limit = 6;
        }
        return $limit;
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

    public function getHelper()
    {
        return $this->_dailydealHelper;
    }

    public function getTodayDealCollection()
    {
        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_dealFactory->create()->getCollection()
            ->addFieldToFilter('status', \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED)
            ->setTodayFilter()
            ->setStoreFilter($storeIds)
            ->setOrder('deal_id', 'DESC');
        return $collection;
    }

    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'url' => $url,
            'product' => $product->getEntityId(),
            \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            'formkey' => $this->_formKey->getFormKey()
        ];
    }

}
