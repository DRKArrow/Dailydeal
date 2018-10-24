<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Index;

use Magento\Framework\App\Action\Action;

class Coming extends Action
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
        $resultPage->getConfig()->getTitle()->set(__('Coming Deals'));
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
                'title' => __('Dailydeal'),
                'link' => $this->_url->getUrl('dailydeal')
            ]
        );
        $breadcrumbs->addCrumb('comingdeal',
            [
                'label' => __('Coming Deal'),
                'title' => __('Coming Deal')
            ]
        );
        return $resultPage;

    }
}
