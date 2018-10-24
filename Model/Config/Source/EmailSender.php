<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Model\Config\Source;

class EmailSender implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'general_contact', 'label' => __('General Contact')],
            ['value' => 'sale_representative', 'label' => __('Sale Representative')],
            ['value' => 'customer_supporter', 'label' => __('Customer Supporter')]
        ];
    }
}
