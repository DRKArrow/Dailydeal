<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Block;

/**
 * "My Ticket" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class HeaderLink extends \Magento\Framework\View\Element\Html\Link
{
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getHref()
    {
        return $this->getUrl('dailydeal', ['_secure' => true]);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getLabel()
    {
        return __('Daily Deals');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_moduleManager->isOutputEnabled('Tigren_Dailydeal')) {
            return '';
        }
        return parent::_toHtml();
    }
}
