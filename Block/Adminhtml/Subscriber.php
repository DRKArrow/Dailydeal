<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml;

class Subscriber extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_subscriber';
        $this->_blockGroup = 'Tigren_Dailydeal';
        $this->_headerText = __('Manage Subscribers');

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Dailydeal::save')) {
            $this->buttonList->update('import', 'label', __('Import Subscribers'));
        } else {
            $this->buttonList->remove('import');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
