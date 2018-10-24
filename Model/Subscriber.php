<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model;

class Subscriber extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'dailydeal_subscriber';

    protected $_cacheTag = 'dailydeal_subscriber';

    /**
     * Prefix of model name
     *
     * @var string
     */
    protected $_subscriberPrefix = 'dailydeal_subscriber';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    public function isExistedEmail($email)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('email', $email);
        if ($collection->getSize() > 0) {
            return true;
        } else {
            return false;
        }
    }

    protected function _construct()
    {
        $this->_init('Tigren\Dailydeal\Model\ResourceModel\Subscriber');
    }

}
