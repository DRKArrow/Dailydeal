<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Plugin;

class UpdateItemQty
{
    protected $_dealFactory;
    protected $_scopeConfig;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $_urlInterface;
    protected $_dailyDealHelper;
    protected $cart;
    protected $_responseFactory;

    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Tigren\Dailydeal\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->_urlInterface = $urlInterface;
        $this->_dailyDealHelper = $helper;
        $this->cart = $cart;
        $this->_responseFactory = $responseFactory;
    }

    public function beforeExecute(
        \Magento\Checkout\Controller\Sidebar\UpdateItemQty $subject
    ) {
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $addedItemId = $subject->getRequest()->getParam('item_id');
            $oldQty = $this->cart->getQuote()->getItemById($addedItemId)->getQty();

            $productId = $this->cart->getQuote()->getItemById($addedItemId)->getProductId();
            if ($addedItemId) {
                $deal = $this->_dealFactory->create()->loadByProductId($productId);
                if ($deal->getId() && $deal->isAvailable()) {
                    $dealRemain = $deal->getQuantity() - $this->_dailyDealHelper->getSoldQuantity($productId);

                    $addedQty = $subject->getRequest()->getParam('item_qty');
                    if ($addedQty > $dealRemain) {
                        $prep = ($dealRemain > 1) ? 'are' : 'is';
                        $dealText = ($dealRemain > 1) ? 'deals' : 'deal';
                        $this->messageManager->addError(__("This product is in deal and there $prep $dealRemain $dealText left."));
                        $subject->getRequest()->setParams(['item_qty' => $oldQty]);
                    }
                }
            }
        }
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
