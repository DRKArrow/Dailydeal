<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Subscriber\Edit;

/**
 * Adminhtml edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscriber_import_form');
        $this->setTitle(__('Import subscribers'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'class' => 'admin__scope-old',
                    'action' => $this->getUrl('dailydeal/subscriber/save'),
                    'method' => 'post'
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}