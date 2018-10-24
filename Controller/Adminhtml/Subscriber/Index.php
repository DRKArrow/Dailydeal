<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Subscriber;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tigren_Dailydeal::manage_subscribers');
        $resultPage->addBreadcrumb(__('Daily Deals'), __('Daily Deals'));
        $resultPage->addBreadcrumb(__('Manage Subscribers'), __('Manage Subscribers'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Subscribers'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Dailydeal::manage_subscribers');
    }

}
