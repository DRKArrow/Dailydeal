<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\Subscriber\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $_subscriber;

    public function __construct(
        \Tigren\Dailydeal\Model\Subscriber $subscriber
    ) {
        $this->_subscriber = $subscriber;
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_subscriber->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

}
