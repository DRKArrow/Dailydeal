<?php

/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Deal;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
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
        $model = $this->_coreRegistry->registry('dailydeal_deal');
        if ($model->getId()) {
            return __("Edit Deal '%1'", $this->escapeHtml($model->getTitle()));
        } else {
            return __('New Deal');
        }
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'deal_id';
        $this->_blockGroup = 'Tigren_Dailydeal';
        $this->_controller = 'adminhtml_deal';

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Dailydeal::save')) {
            $this->buttonList->update('save', 'label', __('Save Deal'));
            $this->buttonList->add(
                'saveandcontinue', [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ], -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Tigren_Dailydeal::deal_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Deal'));
        } else {
            $this->buttonList->remove('delete');
        }
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

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('dailydeal/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

}
