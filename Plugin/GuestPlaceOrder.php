<?php

namespace Tigren\Dailydeal\Plugin;

use Magento\Checkout\Model\GuestPaymentInformationManagement as CheckoutPaymentInformationManagement;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PaymentInformationManagement
 */
class GuestPlaceOrder
{
    /**
     * @var CartManagementInterface
     */
    protected $dealFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Tigren\Dailydeal\Helper\Data
     */
    protected $_dailydealHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cart;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $messageManager;

    /**
     * PaymentInformationManagement constructor.
     * @param CartManagementInterface $cartManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        \Magento\Checkout\Model\Cart $cart,
        \Tigren\Dailydeal\Helper\Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\Message\Manager $messageManager
    )
    {
        $this->dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->_dailydealHelper = $helper;
        $this->cart = $cart;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param CheckoutPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return int
     * @throws CouldNotSaveException
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        CheckoutPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    )
    {
        if ($this->getScopeConfig('dailydeal/general/enable')) {
            $products = $this->cart->getQuote()->getAllVisibleItems();
            $errorCount = 0;
            foreach ($products as $product) {
                $productId = $product->getProduct()->getId();
                $deal = $this->dealFactory->create()->loadByProductId($productId);
                if ($deal->getId() && $deal->isAvailable()) {
                    $dealRemain = $deal->getQuantity() - $this->_dailydealHelper->getSoldQuantity($productId);

                    $addedQty = $product->getQty();
                    if ($addedQty > $dealRemain) {
                        $prep = ($dealRemain > 1) ? 'are' : 'is';
                        $dealText = ($dealRemain > 1) ? 'deals' : 'deal';
                        $productName = $product->getName();
                        $this->messageManager->addError(__("$productName is in deal and there $prep $dealRemain $dealText left."));
                        $errorCount++;
                    }
                }
            }

            if ($errorCount > 0) {
                $redirect = $this->_url->getUrl('checkout/cart');
                $this->_responseFactory->create()->setRedirect($redirect)->sendResponse();
                return false;
            }
        }
        return $proceed($cartId, $email, $paymentMethod);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}