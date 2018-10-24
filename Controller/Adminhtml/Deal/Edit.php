<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Deal;

class Edit extends \Magento\Backend\App\Action
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
     * Edit Action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('deal_id');
        $deal = $this->_objectManager->create('Tigren\Dailydeal\Model\Deal');

        if ($id) {
            $deal->load($id);
            if (!$deal->getId()) {
                $this->messageManager->addError(__('This deal no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $deal->setData($data);
        }

        $this->_coreRegistry->register('dailydeal_deal', $deal);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Deal') : __('New Deal'), $id ? __('Edit Deal') : __('New Deal')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Daily Deals'));
        $resultPage->getConfig()->getTitle()
            ->prepend($deal->getId() ? __('Edit Deal ') . $deal->getTitle() : __('New Deal'));

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
        $resultPage->setActiveMenu('Tigren_Dailydeal::manage_deals')
            ->addBreadcrumb(__('Daily Deals'), __('Daily Deals'))
            ->addBreadcrumb(__('Manage Deals'), __('Manage Deals'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Dailydeal::save');
    }

}
