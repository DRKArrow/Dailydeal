<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Helper;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const XML_PATH_CONFIRM_EMAIL = 'dailydeal/subscription/confirm_email_template';
    /**
     *
     */
    const XML_PATH_DAILY_NOTIFY_EMAIL = 'dailydeal/subscription/email_template';

    /**
     *
     */
    const CACHE_TODAY_DEALS = 'MB_CACHE_TODAY_DEALS';

    /**
     * @var array
     */
    protected $_localDeals = [];
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockItem;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $prdImageHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Tigren\Dailydeal\Model\SubscriberFactory
     */
    protected $_subscriberFactory;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;
    /**
     * @var \Tigren\Dailydeal\Model\DealFactory
     */
    protected $_dealFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockItem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Tigren\Dailydeal\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Tigren\Dailydeal\Model\DealFactory $dealFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Tigren\Dailydeal\Model\SubscriberFactory $subscriberFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\UrlFactory $urlFactory,
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
        $this->_stockItem = $stockItem;
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->prdImageHelper = $imageHelper;
        $this->_customerSession = $customerSession;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->urlModel = $urlFactory->create();
        $this->_dealFactory = $dealFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getBaseCurrencySymbol()
    {
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        return $this->_localeCurrency->getCurrency($baseCurrencyCode)->getSymbol();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getCode();
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductPrice($product)
    {
        if ($product && $product->getId()) {
            return number_format($product->getPrice(), 2, '.', '');
        }
        return '0.00';
    }

    /**
     * @param $product
     * @return string
     */
    public function getFinalProductPrice($product)
    {
        if ($product && $product->getId()) {
            return number_format($product->getFinalPrice(), 2, '.', '');
        }
        return '0.00';
    }

    /**
     * @param $product
     * @return float|int
     */
    public function getProductQuantity($product)
    {
        if ($product && $productId = $product->getId()) {
            //change logic get quantity
            $qty = 0;
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child) {
                $childQty = $this->_stockItem->getStockQty($child->getId(), $child->getStore()->getWebsiteId());
                $qty += $childQty;
            }
            return $qty;
        }
        return 0;
    }

    /**
     * @param $product
     * @return float|int
     */
    public function calSaving($product)
    {
        $isRoundSaving = $this->getScopeConfig('dailydeal/general/is_round_saving');
        $saving = 0;
        if ($product && $product->getPrice() > 0) {
            $deal = $this->_dealFactory->create()->loadByProductId($product->getId());
            $decrease = floatval($product->getPrice()) - floatval($deal->getPrice());
            if ($isRoundSaving) {
                $saving = round(100 * $decrease / floatval($product->getPrice()), 0);
            } else {
                $saving = round(100 * $decrease / floatval($product->getPrice()), 2);
            }
        }
        if ($product && $product->getTypeId() == 'configurable') {
            $deal = $this->_dealFactory->create()->loadByProductId($product->getId());
            $basePrice = $product->getPriceInfo()->getPrice('regular_price')->getMinRegularAmount()->getValue();
            $decrease = floatval($basePrice) - floatval($deal->getPrice());
            $saving = round(100 * $decrease / floatval($basePrice), 0);
        }

        return $saving;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeConfig($path)
    {
        $storeId = $this->getCurrentStoreId();
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductById($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    /**
     * @param $price
     * @return int
     */
    public function getPriceWithCurrency($price)
    {
        if ($price) {
            return $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency(number_format($price,
                2, '.', ''), true, false);
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getCurrentTime()
    {
        return $this->_date->gmtTimestamp() + $this->_date->getGmtOffset();
    }

    /**
     * @param $localTime
     * @return int
     */
    public function getGmtTime($localTime)
    {
        return $localTime - $this->_date->getGmtOffset();
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomerId();
        }
        return null;
    }

    /**
     * @param $product
     * @param $size
     * @return string
     */
    public function getProductImageUrl($product, $size)
    {
        $imageSize = 'product_page_image_' . $size;
        if ($size == 'category') {
            $imageSize = 'category_page_list';
        }
        $imageUrl = $this->prdImageHelper->init($product, $imageSize)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->getUrl();
        return $imageUrl;
    }

    /**
     * @param $subscriberData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendSubscriptionEmail($subscriberData)
    {
        $confirmLink = $this->urlModel->getUrl('dailydeal/subscribe/confirm',
            ['subscriber_id' => $subscriberData['subscriber_id'], 'confirm_code' => $subscriberData['confirm_code']]);
        $vars = [];
        $vars['customer_name'] = $subscriberData['customer_name'];
        $vars['confirm_link'] = $confirmLink;
        $emailSender = $this->getScopeConfig('dailydeal/subscription/email_sender');
        $storeId = $this->getCurrentStoreId();
        $this->inlineTranslation->suspend();
        try {
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->getScopeConfig(self::XML_PATH_CONFIRM_EMAIL))
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($vars)
                ->setFrom($emailSender)
                ->addTo($subscriberData['email'], $subscriberData['customer_name'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendTodayDealEmail()
    {
        $subscribers = $this->_subscriberFactory->create()->getCollection()->addFieldToFilter('status',
            \Tigren\Dailydeal\Model\Subscriber::STATUS_ENABLED);
        if ($subscribers->getSize() > 0) {
            $dealsLink = $this->urlModel->getUrl('dailydeal');
            $storeId = $this->getCurrentStoreId();
            $emailSender = $this->getScopeConfig('dailydeal/subscription/email_sender');
            foreach ($subscribers->getItems() as $subscriber) {
                $email = $subscriber->getEmail();
                $customerName = $subscriber->getCustomerName();
                $unsubscribeLink = $this->urlModel->getUrl('dailydeal/subscribe/unsubscribe',
                    ['subscriber_id' => $subscriber->getId(), 'confirm_code' => $subscriber->getConfirmCode()]);
                $vars = [];
                $vars['customer_name'] = $customerName;
                $vars['deals_link'] = $dealsLink;
                $vars['unsubscribe_link'] = $unsubscribeLink;
                $this->inlineTranslation->suspend();
                try {
                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($this->getScopeConfig(self::XML_PATH_DAILY_NOTIFY_EMAIL))
                        ->setTemplateOptions([
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeId
                        ])
                        ->setTemplateVars($vars)
                        ->setFrom($emailSender)
                        ->addTo($email, $customerName)
                        ->getTransport();

                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function getLocalDeals()
    {
        if ($this->_localDeals) {
            return $this->_localDeals;
        }
        $cache = $this->_objectManager->get('\Magento\Framework\App\Cache');

        if (($data = $cache->load(self::CACHE_TODAY_DEALS)) !== false) {
            $this->_localDeals = unserialize($data);
        } else {
            $deals = $this->_dealFactory->create()->getTodayDealsEndTime();
            foreach ($deals as $deal) {
                $this->_localDeals[$deal['product_id']] = strtotime($deal['end_time']);
            }
            $cache->save(serialize($this->_localDeals), self::CACHE_TODAY_DEALS, ['local_deals'], 7200);
        }

        return $this->_localDeals;
    }

    /**
     *
     */
    public function refreshLocalDeals()
    {
        $cache = $this->_objectManager->get('\Magento\Framework\App\Cache');
        $cache->remove(self::CACHE_TODAY_DEALS);
        $deals = $this->_dealFactory->create()->getTodayDealsEndTime();
        foreach ($deals as $deal) {
            $this->_localDeals[$deal['product_id']] = strtotime($deal['end_time']);
        }
        $cache->save(serialize($this->_localDeals), self::CACHE_TODAY_DEALS, ['local_deals'], 7200);
    }

    public function getSoldQuantity($product)
    {
        if(is_object($product))
            $productId = $product->getId();
        else
            $productId = $product;

        $_objectManager = $this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection = $_objectManager->getConnection();
        $tableName = 'tigren_dailydeal_deal_product';

        $select = $connection->select()
            ->from($tableName, 'deal_product_sold')
            ->where('product_id = ?', $productId);

        $result = $connection->fetchOne($select);

        if($result)
            return $result;
        else
            return 0;
    }
}