<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Deals;

class ComingDeals extends \Magento\Framework\View\Element\Template
{

    protected $_dealFactory;
    protected $_dailydealHelper;
    protected $_deals;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dealFactory = $dealFactory;
        $this->_dailydealHelper = $dailydealHelper;

        $this->_deals = $this->getComingDealCollection();
    }

    public function getComingDealCollection()
    {
        $storeIds = [0, $this->getCurrentStoreId()];
        $collection = $this->_dealFactory->create()->getCollection()
            ->addFieldToFilter('status', \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED)
            ->setComingFilter()
            ->setStoreFilter($storeIds)
            ->setOrder('start_time', 'ASC');
        return $collection;
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    public function getIdentities()
    {
        return [\Tigren\Dailydeal\Model\Deal::CACHE_TAG . '_' . 'coming'];
    }

    public function getLimit()
    {
        $limit = (int)$this->getScopeConfig('dailydeal/general/deal_per_page');
        if (empty($limit)) {
            $limit = 9;
        }
        return $limit;
    }

    public function getScopeConfig($path)
    {
        $storeId = $this->getCurrentStoreId();
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getHelper()
    {
        return $this->_dailydealHelper;
    }

    public function getPagedDeals()
    {
        return $this->_deals;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_deals) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'dailydeal.deal.coming.pager')
                ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50, 100 => 100])
                ->setCollection($this->_deals);
            $this->setChild('pager', $pager);
            $this->_deals->load();
        }
        return $this;
    }

}
