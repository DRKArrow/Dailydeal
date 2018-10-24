<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Deal;

class Report extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('deal_id');
        $deal = $this->_objectManager->create('Tigren\Dailydeal\Model\Deal');
        if ($id) {
            $deal->load($id);
            if (!$deal->getId()) {
                $this->messageManager->addError(__('This deal no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('dailydeal_deal', $deal);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Tigren_Dailydeal::manage_deals');
        $resultPage->addBreadcrumb(__('Dailydeal'), __('Dailydeal'));
        $resultPage->addBreadcrumb(__('Manage Deals'), __('Manage Deals'));
        $resultPage->addBreadcrumb(__('Report Deal'), __('Report Deal'));
        $resultPage->getConfig()->getTitle()->prepend(__('Daily Deals'));
        $resultPage->getConfig()->getTitle()->prepend(__('Report Deal ') . $deal->getTitle());

        return $resultPage;
    }

    /**
     * Is the user allowed to view the grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Dailydeal::manage_deals');
    }

}
