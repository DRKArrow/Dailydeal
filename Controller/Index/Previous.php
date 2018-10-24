<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Index;

use Magento\Framework\App\Action\Action;

class Previous extends Action
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
        $resultPage->getConfig()->getTitle()->set(__('Previous Deals'));
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
        $breadcrumbs->addCrumb('previousdeal',
            [
                'label' => __('Previous Deal'),
                'title' => __('Previous Deal')
            ]
        );
        return $resultPage;

    }
}
