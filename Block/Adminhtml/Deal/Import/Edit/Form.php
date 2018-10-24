<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Deal\Import\Edit;

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
        $this->setId('deal_import_form');
        $this->setTitle(__('Import Deals'));
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
                    'action' => $this->getUrl('dailydeal/deal/saveImport'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]

            ]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Import deals from a CSV file'), 'class' => 'fieldset-wide']
        );

        $csvExampleFile = $this->getViewFileUrl('Tigren_Dailydeal::files/deals.csv');
        $fieldset->addField(
            'deal_csv_file',
            'file',
            [
                'name' => 'deal_csv_file',
                'label' => __('Choose CSV file to import'),
                'title' => __('Choose CSV file to import'),
                'required' => true,
                'after_element_html' => __('<br/>A CSV file include some deals for example (<a href="' . $csvExampleFile . '">Download</a>)')
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}