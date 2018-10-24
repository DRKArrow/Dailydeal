<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Cron;

class SendDailyEmail
{
    private $_dailydealHelper;

    public function __construct(
        \Tigren\Dailydeal\Helper\Data $dailydealHelper
    ) {
        $this->_dailydealHelper = $dailydealHelper;
    }

    /**
     * Cron job method to send email everyday
     *
     * @return void
     */
    public function execute()
    {
        $this->_dailydealHelper->sendTodayDealEmail();
    }
}
