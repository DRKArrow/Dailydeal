<?php

/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Subscriber;

class Import extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve text for header element depending on loaded blocklist
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Import Subscriber');
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'subscriber_id';
        $this->_blockGroup = 'Tigren_Dailydeal';
        $this->_controller = 'adminhtml_subscriber_import';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('delete');
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }


}
