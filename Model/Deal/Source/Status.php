<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\Deal\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $_deal;

    public function __construct(
        \Tigren\Dailydeal\Model\Deal $deal
    ) {
        $this->_deal = $deal;
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_deal->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

}
