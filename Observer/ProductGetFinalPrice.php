<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductGetFinalPrice implements ObserverInterface
{

    protected $_dealFactory;
    protected $_scopeConfig;
    protected $_productFactory;

    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $prductFactory
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_productFactory = $prductFactory;
    }

    public function execute(Observer $observer)
    {
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $product = $observer->getProduct();
            $deal = $this->_dealFactory->create()->loadByProductId($product->getId());
            if ($deal->getId() && $deal->isAvailable()) {
                $startTime = date('Y-m-d H:i:s', strtotime($deal->getStartTime()) - 86400);
                $endTime = date('Y-m-d H:i:s', strtotime($deal->getEndTime()) - 86400);
                $product->setSpecialFromDate($startTime);
                $product->setSpecialToDate($endTime);
                $product->setSpecialPrice($deal->getPrice());
                $product->setFinalPrice($deal->getPrice());
            }
        }
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
