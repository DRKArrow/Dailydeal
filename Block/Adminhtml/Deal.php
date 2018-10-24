<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml;

class Deal extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_deal';
        $this->_blockGroup = 'Tigren_Dailydeal';
        $this->_headerText = __('Manage Deals');

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Dailydeal::save')) {
            $this->buttonList->update('add', 'label', __('Add New Deal'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
