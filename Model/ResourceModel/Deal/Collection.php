<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\ResourceModel\Deal;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

    }

    public function setStoreFilter($storeIds)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            return $this;
        }
        $connection = $this->getConnection();
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds === null ? -1 : $storeIds];
        }
        if (empty($storeIds)) {
            return $this;
        }
        $this->getSelect()->distinct(true)->join(
            ['store_table' => $this->getTable('tigren_dailydeal_deal_store')],
            'main_table.deal_id = store_table.deal_id',
            []
        );
        $inCondition = $connection->prepareSqlCondition('store_table.store_id', ['in' => $storeIds]);
        $this->getSelect()->where($inCondition);
        return $this;
    }

    public function setComingFilter()
    {
        $this->getSelect()->where('TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(),main_table.start_time) > 0')
            ->where('(main_table.quantity - main_table.sold) > 0');
        return $this;
    }

    public function setTodayFilter()
    {
        $this->getSelect()
            ->where('TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(),main_table.start_time) < 0')
            ->where('TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(),main_table.end_time) > 0')
            ->where('(main_table.quantity - main_table.sold) > 0');
        return $this;
    }

    public function setPreviousFilter()
    {
        $this->getSelect()
            ->where('TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(),main_table.end_time) < 0 OR (main_table.quantity - main_table.sold) <= 0');
        return $this;
    }

    public function setRemain()
    {
        $this->getSelect()
            ->where('(main_table.quantity - main_table.sold) > 0');
        return $this;
    }

    public function limit($limit)
    {
        $this->getSelect()->limit($limit);
        return $this;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tigren\Dailydeal\Model\Deal', 'Tigren\Dailydeal\Model\ResourceModel\Deal');
        $this->_idFieldName = 'deal_id';
    }


}
