<?php

/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Subscriber\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Main extends Generic
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $subscriber = $this->_coreRegistry->registry('dailydeal_subscriber');
        $subscriberId = $subscriber->getId();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('subscriber_');

        $fieldset = $form->addFieldset(
            'base_fieldset', ['legend' => __('Subscriber Information'), 'class' => 'fieldset-wide']
        );

        if ($subscriberId) {
            $fieldset->addField('subscriber_id', 'hidden', ['name' => 'subscriber_id']);
        }

        $fieldset->addField(
            'customer_name', 'text', [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'email', 'text', [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,
                'class' => __('validate-email')
            ]
        );
        $fieldset->addField(
            'status', 'select', [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => [
                    \Tigren\Dailydeal\Model\Subscriber::STATUS_ENABLED => __('Enabled'),
                    \Tigren\Dailydeal\Model\Subscriber::STATUS_DISABLED => __('Disabled')
                ],
                'required' => true
            ]
        );


        if (!$subscriber->getId()) {                         //Add new
            $subscriber->setData('status', '1');
        }

        $form->setValues($subscriber->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
