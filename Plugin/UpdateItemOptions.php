<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Plugin;

class UpdateItemOptions
{
    protected $_dealFactory;
    protected $_scopeConfig;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $_urlInterface;
    protected $_dailyDealHelper;

    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Tigren\Dailydeal\Helper\Data $helper
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->_urlInterface = $urlInterface;
        $this->_dailyDealHelper = $helper;
    }

    public function aroundExecute(
        \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject,
        \Closure $proceed
    ) {
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $addedItemId = $subject->getRequest()->getParam('product');
            if ($addedItemId) {
                $deal = $this->_dealFactory->create()->loadByProductId($addedItemId);
                if ($deal->getId() && $deal->isAvailable()) {
                    $dealRemain = $deal->getQuantity() - $this->_dailyDealHelper->getSoldQuantity($addedItemId);

                    $addedQty = $subject->getRequest()->getParam('qty');
                    if ($addedQty > $dealRemain) {
                        $prep = ($dealRemain > 1) ? 'are' : 'is';
                        $dealText = ($dealRemain > 1) ? 'deals' : 'deal';
                        $this->messageManager->addError(__("This product is in deal and there $prep $dealRemain $dealText left."));
                        return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                    }
                }
            }
        }
        return $proceed();
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
