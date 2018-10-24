<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block\Adminhtml\Deal\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    public function getDeal()
    {
        if (!$this->getData('dailydeal_deal') instanceof \Tigren\Dailydeal\Model\Deal) {
            $this->setData('dailydeal_deal', $this->_coreRegistry->registry('dailydeal_deal'));
        }
        return $this->getData('dailydeal_deal');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('dailydeal_deal_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Deal'));
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            'product',
            [
                'label' => __('Products'),
                'url' => $this->getUrl('dailydeal/*/productgrid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'main',
            [
                'label' => __('Information'),
                'content' => $this->getLayout()->createBlock(
                    'Tigren\Dailydeal\Block\Adminhtml\Deal\Edit\Tab\Main'
                )->toHtml()
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }
}