<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Subscriber;

class Import extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(__('Import Subscriber'), __('Import Subscriber'));
        $resultPage->getConfig()->getTitle()->prepend(__('Daily Deals'));
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Import Subscriber'));

        return $resultPage;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tigren_Dailydeal::manage_subscribers')
            ->addBreadcrumb(__('Dailydeal'), __('Dailydeal'))
            ->addBreadcrumb(__('Manage Subscribers'), __('Manage Subscribers'));
        return $resultPage;
    }

}
