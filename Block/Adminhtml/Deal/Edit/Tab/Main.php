<?php

/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Deal\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Main extends Generic
{

    protected $_systemStore;
    protected $_dailydealHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Tigren\Dailydeal\Helper\Data $dailydealHelper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_dailydealHelper = $dailydealHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $deal = $this->_coreRegistry->registry('dailydeal_deal');
        $dealId = $deal->getId();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('deal_');

        $fieldset = $form->addFieldset(
            'base_fieldset', ['legend' => __('Deal Information'), 'class' => 'fieldset-wide']
        );

        if ($dealId) {                                                          //Edit
            $fieldset->addField('deal_id', 'hidden', ['name' => 'deal_id']);
            $productIds = $deal->getProductIds();
            $product = $this->_dailydealHelper->getProductById($productIds[0]);
            if ($productIds && is_array($productIds) && count($productIds) > 0) {
                $deal->setData('prd_price', $product->getPrice());
                $deal->setData('prd_qty', $deal->getProductQty());
            }
        } else {                                                                //Add new
            $fieldset->addField('is_select_product', 'hidden', [
                'name' => 'is_select_product',
                'class' => __('validate-is-select-product')
            ]);
        }

        $fieldset->addField(
            'prd_price', 'text', [
                'name' => 'prd_price',
                'label' => __('Product Price (' . $this->_dailydealHelper->getBaseCurrencySymbol() . ')'),
                'title' => __('Product Price'),
                'readonly' => 'readonly',
                'style' => 'border-width: 1px !important'
            ]
        );
        $fieldset->addField(
            'prd_qty', 'text', [
                'name' => 'prd_qty',
                'label' => __('Quantity in Stock'),
                'title' => __('Quantity in Stock'),
                'readonly' => 'readonly'
            ]
        );

//        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
//        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
//        $dateTimeFormat = $this->_localeDate->getDateTimeFormat(\IntlDateFormatter::SHORT);
        $dateFormat = 'M/d/yy';
        $timeFormat = 'h:mm a';
        $dateTimeFormat = 'M/d/yy h:mm a';
        $style = 'color: #000;background-color: #fff; font-weight: bold; font-size: 13px;';

        $fieldset->addField(
            'title', 'text', [
                'name' => 'title',
                'label' => __('Deal Title'),
                'title' => __('Deal Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'start_time', 'date', [
                'name' => 'start_time',
                'label' => __('Start Time'),
                'title' => __('Start Time'),
                'style' => $style,
                'required' => true,
                'class' => __('validate-date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $dateTimeFormat
            ]
        );

        $fieldset->addField(
            'end_time', 'date', [
                'name' => 'end_time',
                'label' => __('End Time'),
                'title' => __('End Time'),
                'style' => $style,
                'required' => true,
                'class' => __('validate-date validate-valid-time'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => $dateTimeFormat
            ]
        );

        $fieldset->addField(
            'price', 'text', [
                'name' => 'price',
                'label' => __('Deal Price (' . $this->_dailydealHelper->getBaseCurrencySymbol() . ')'),
                'title' => __('Deal Price'),
                'required' => true,
                'class' => __('validate-zero-or-greater validate-price'),
                'style' => 'border-width: 1px !important'
            ]
        );
        $fieldset->addField(
            'quantity', 'text', [
                'name' => 'quantity',
                'label' => __('Quantity for sale via Daily Deal'),
                'title' => __('Quantity for sale via Daily Deal'),
                'required' => true,
                'class' => __('validate-greater-than-zero validate-quantity')
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores', 'multiselect', [
                    'label' => __('Store View'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $deal->setSelectStores($deal->getStores());
        } else {
            $fieldset->addField(
                'select_stores', 'hidden', [
                    'name' => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $deal->setSelectStores($this->_storeManager->getStore(true)->getId());
        }
        $getJs = $this->_getProductInfo();
        $getJs .= $this->_addValidation();
        $fieldset->addField(
            'status', 'select', [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => [
                    \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED => __('Enabled'),
                    \Tigren\Dailydeal\Model\Deal::STATUS_DISABLED => __('Disabled')
                ],
                'after_element_html' => $getJs,
            ]
        );

        if (!$deal->getId()) {                                                  //Add new
            $deal->setData('status', \Tigren\Dailydeal\Model\Deal::STATUS_ENABLED);
        }

        $form->setValues($deal->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getProductInfo()
    {
        return <<<HTML
    <script type='text/javascript'>
        require(['jquery'], function ($) {
            $(document).ready(function () {
                $('body').on('click', 'table#deal_product_grid_table tbody tr._clickable', function() {
                    var checkboxes = $('table#deal_product_grid_table').children('tbody').find('input:checkbox:checked');
                    if (checkboxes.length == 0) {
                        $('#deal_prd_price').val('');
                        $('#deal_prd_qty').val('');
                        $('#deal_is_select_product').val('');
                    }
                    else if (checkboxes.length == 1) {
                        var price = checkboxes.closest('tr').children('td.col-product_price').text().trim();
                        var qty = checkboxes.closest('tr').children('td.col-product_qty').text().trim();
                        $('#deal_prd_price').val(price);
                        $('#deal_prd_qty').val(qty);
                        $('#deal_is_select_product').val('1');
                    } else {
                        var priceArr = [];
                        var qtyTotal = 0;
                        checkboxes.each(function() {
                            var price = $(this).closest('tr').children('td.col-product_price').text().trim();
                            var qty = $(this).closest('tr').children('td.col-product_qty').text().trim();
                            priceArr.push(price);
                            qtyTotal += parseFloat(qty);
                        });
                        var minPrice = calMin(priceArr);
                        $('#deal_prd_price').val(minPrice);
                        $('#deal_prd_qty').val(qtyTotal);
                        $('#deal_is_select_product').val('1');
                    }
                });
                
                function calMin(arr) {
                    var min = arr.pop();
                    for (var i in arr) {
                        if (parseFloat(arr[i]) < min) {
                            min = parseFloat(arr[i]);
                        }
                    }
                    return min;
                }
            });
        });
    </script>
HTML;
    }

    protected function _addValidation()
    {
        return <<<HTML
    <script type='text/javascript'>
        require(['jquery', 'jquery/ui', 'jquery/validate', 'mage/translate' ], function($){ 
            $.validator.addMethod('validate-valid-time', function (value) {
                var startTime = $('#deal_start_time').val();
                startTime = (new Date(startTime)).getTime();
                var endTime = $('#deal_end_time').val();
                endTime = (new Date(endTime)).getTime();
                if (startTime && endTime) {
                    return (startTime < endTime); 
                } else {
                    return true;
                }
            }, $.mage.__('End Time must be later than Start Time'));
            $.validator.addMethod('validate-is-select-product', function (value) {
                return value;
            }, $.mage.__('You must select product before saving'));
            $.validator.addMethod('validate-price', function (value) {
                var productPrice = $('#deal_prd_price').val();
                if (productPrice && value) {
                    return (parseFloat(value) < parseFloat(productPrice)); 
                } else {
                    return true;
                }
            }, $.mage.__('Price of Deal must be smaller than Product Price'));
       
        });
    </script>
HTML;
    }

}
