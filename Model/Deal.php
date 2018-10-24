<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model;

class Deal extends \Magento\Framework\Model\AbstractModel
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'dailydeal_deal';

    protected $_cacheTag = 'dailydeal_deal';

    /**
     * Prefix of model name
     *
     * @var string
     */
    protected $_dealPrefix = 'dailydeal_deal';

    protected $_scopeConfig;
    protected $_productFactory;
    protected $_stockItem;
    protected $_stockItemRepository;
    protected $_date;
    protected $_dailydealHelper;
    protected $urlModel;
    protected $_formKey;
    protected $_orderItemCollectionFactory;
    protected $cart;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_productFactory = $productFactory;
        $this->stockItem = $stockItem;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_date = $date;
        $this->_dailydealHelper = $dailydealHelper;
        $this->urlModel = $urlFactory->create();
        $this->_formKey = $formKey;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->cart = $cart;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    public function getAvailableProgressStatuses()
    {
        return [
            'running' => __('Running'),
            'coming' => __('Coming'),
            'ended' => __('Ended')
        ];
    }

    public function getProductPrice()
    {
        $product = $this->_productFactory->create();
        $minProductPrice = 0;
        $productIds = $this->getProductIds();
        if ($productIds && is_array($productIds) && count($productIds) > 0) {
            $productPrices = [];
            foreach ($productIds as $productId) {
                $product->load($productId);
                $productPrices[] = $product->getPrice();
            }
            $minProductPrice = min($productPrices);
        }
        return number_format($minProductPrice, 2, '.', '');
    }

    public function getProductQty()
    {
        $product = $this->_productFactory->create();
        $productQty = 0;
        $productIds = $this->getProductIds();
        if ($productIds && is_array($productIds) && count($productIds) > 0) {
            foreach ($productIds as $productId) {
                $product->load($productId);
//                $productQty += $this->stockItem->getStockQty($productId, $product->getStore()->getWebsiteId());
                $productQty += $this->_stockItemRepository->getStock($productId)->getQty();
            }
        }
        return number_format($productQty, 2, '.', '');
    }

    public function loadByProductId($productId)
    {
        $dealId = $this->getResource()->getDealByProductId($productId);
        return $this->load($dealId);
    }

    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isAvailable()
    {
        $nowTime = $this->_dailydealHelper->getCurrentTime();
        $startTime = strtotime($this->getStartTime());
        $endTime = strtotime($this->getEndTime());
        $remain = $this->getQuantity() - $this->getSold();

        $totalInCart = 0;
        $dealProductIds = $this->getProductIds();
        $cart = $this->cart->getQuote();
        foreach ($cart->getAllItems() as $item) {
            $productId = $item->getProductId();
            if (in_array($productId, $dealProductIds)) {
                $totalInCart += $item->getQty();
            }
        }

        if (($this->getStatus() == \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED) && ($remain > 0) && ($startTime <= $nowTime) && ($nowTime <= $endTime)) {
            return true;
        }
        return false;
    }

    public function isNotEnded()
    {
        $nowTime = $this->_dailydealHelper->getCurrentTime();
        $startTime = strtotime($this->getStartTime());
        $endTime = strtotime($this->getEndTime());
        $remain = $this->getQuantity() - $this->getSold();
        if (($this->getStatus() == \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED) && ($remain > 0) && ($nowTime <= $endTime)) {
            return true;
        }
        return false;
    }

    public function getOrderItemCollection()
    {
        $productIds = $this->getProductIds();
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();
        $collection = $this->_orderItemCollectionFactory->create()
            ->addFieldToFilter('product_id', ['in' => $productIds]);
        $collection->getSelect()
            ->where("TIMESTAMPDIFF(SECOND,'$startTime',main_table.created_at) > 0")
            ->where("TIMESTAMPDIFF(SECOND,'$endTime',main_table.created_at) < 0");
        return $collection;
    }

    public function getTodayDealsEndTime()
    {
        return $this->getResource()->getTodayDealsEndTime();
    }

    public function getTitleFromProducts()
    {
        $product = $this->_productFactory->create();
        $title = '';
        $productIds = $this->getProductIds();
        foreach ($productIds as $productId) {
            $product->load($productId);
            $title .= $product->getName() . ',';
        }
        $title = rtrim($title, ',');
        return $title;
    }

    protected function _construct()
    {
        $this->_init('Tigren\Dailydeal\Model\ResourceModel\Deal');
    }
}
