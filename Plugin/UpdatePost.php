<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Plugin;

class UpdatePost
{
    protected $_dealFactory;
    protected $_scopeConfig;
    protected $cart;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $_urlInterface;
    protected $_dailyDealHelper;

    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\Manager $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Tigren\Dailydeal\Helper\Data $helper
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->cart = $cart;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->_urlInterface = $urlInterface;
        $this->_dailyDealHelper = $helper;
    }

    public function aroundExecute(
        \Magento\Checkout\Controller\Cart\UpdatePost $subject,
        \Closure $proceed
    ) {
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $updateAction = $subject->getRequest()->getParam('update_cart_action');
            if ($updateAction != 'empty_cart') {
                $cartData = $subject->getRequest()->getParam('cart');
                $isValid = true;
                foreach ($cartData as $key => $data) {
                    $productId = $this->cart->getQuote()->getItemById($key)->getProductId();
                    $deal = $this->_dealFactory->create()->loadByProductId($productId);
                    if ($deal->getId() && $deal->isAvailable()) {
                        $dealRemain = $deal->getQuantity() - $this->_dailyDealHelper->getSoldQuantity($this->cart->getQuote()->getItemById($key)->getProduct()->getId());
                        if ($data['qty'] > $dealRemain) {
                            $isValid = false;
                            break;
                        }
                    }
                }
                if (!$isValid) {
                    $this->messageManager->addError(__("We can't update the shopping cart because quantity is not valid."));
                    return $this->resultRedirectFactory->create()->setPath('checkout/cart');
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
