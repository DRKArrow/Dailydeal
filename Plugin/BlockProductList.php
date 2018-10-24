<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Plugin;

class BlockProductList
{
    const CACHE_TODAY_DEALS = 'MB_CACHE_TODAY_DEALS';

    protected $_scopeConfig;
    protected $_dealFactory;
    protected $_dailydealHelper;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dealFactory = $dealFactory;
        $this->_dailydealHelper = $dailydealHelper;
        $this->_objectManager = $objectManager;
    }

    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $cache = $this->_objectManager->get('\Magento\Framework\App\Cache');
            if (($data = $cache->load(self::CACHE_TODAY_DEALS)) !== false) {
                $localDeals = unserialize($data);
            } else {
                $localDeals = $this->_dailydealHelper->getLocalDeals();
            }
            $productId = $product->getId();
            $deal = $this->_dealFactory->create()->loadByProductId($productId);
            if (!empty($localDeals[$productId]) && $deal->isAvailable()) {
                $endTime = $localDeals[$productId];
                $result .= '
                <div class="dailydeal-cat timeleft-block">
                    <label>' . __('DEAL TIME') . '</label>
                    <span class="timeleft-cat" data-totime="' . $endTime . '"> </span>
                </div>
                <script type="text/javascript">
                    require(["jquery", "dailydeal_countdown"], function ($) {
                        $(document).ready(function () {
                            $(".timeleft-cat").dealcountdown();
                        });
                    });
                </script>
                ';
            }
        }
        return $result;
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
