<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class AfterOrderPlaced implements ObserverInterface
{

    protected $_dealFactory;
    protected $_scopeConfig;
    protected $_dailydealHelper;
    protected $logger;
    protected $_connection;

    public function __construct(
        \Tigren\Dailydeal\Model\DealFactory $dealFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        \Psr\Log\LoggerInterface $logger,
        ResourceConnection $connection
    ) {
        $this->_dealFactory = $dealFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_dailydealHelper = $dailydealHelper;
        $this->logger = $logger;
        $this->_connection = $connection;
    }

    public function execute(Observer $observer)
    {
        $orderModel = $observer->getOrder();
        $items = $orderModel->getAllItems();

        foreach ($items as $item) {
            if ($this->getScopeConfig('dailydeal/general/enable')) {
                $product = $this->_dailydealHelper->getProductById($item->getProductId());
                if($product->getTypeId() == 'simple') {
                    continue;
                }
                $deal = $this->_dealFactory->create()->loadByProductId($item->getProductId());
                if ($deal->getId()) {
                    if ($deal->isAvailable()) {
                        $orderQty = $item->getQtyOrdered();
                        $_children = $product->getTypeInstance()->getUsedProducts($product);
                        $ids = [];
                        foreach($_children as $child) {
                            $ids[] = $child->getId();
                        }

                        $ids[] = $product->getId();

                        $connection = $this->_connection->getConnection();
                        $tableName = 'tigren_dailydeal_deal_product';
                        $select = $connection->select()
                            ->from($tableName, 'deal_product_sold')
                            ->where('deal_id = ?', $deal->getId())
                            ->where('product_id = ?', $product->getId());

                        $sold = $connection->fetchOne($select);

                        $update = ['deal_product_sold' => (int)$sold + $orderQty];

                        try {
                            $connection->update($tableName, $update, "deal_id = '" . $deal->getId() . "' and product_id in (" . implode(',' , $ids)  . ")");
                            $this->_dailydealHelper->refreshLocalDeals();
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
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
