<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Deal;

class ProductGrid extends \Magento\Backend\App\Action
{
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Get grid and serializer block
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('deal_edit_tab_productgrid')
            ->setProductIds($this->getRequest()->getPost('product_ids', null));
        return $resultLayout;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Dailydeal::manage_deals');
    }
}
