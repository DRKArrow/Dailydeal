<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Index;

use Magento\Framework\App\Action\Action;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Daily Deals'));
        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb('dailydeal',
            [
                'label' => __('Dailydeal'),
                'title' => __('Dailydeal')
            ]
        );
        return $resultPage;

    }
}
