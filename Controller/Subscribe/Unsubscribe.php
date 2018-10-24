<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Subscribe;

use Magento\Framework\App\Action\Action;

class Unsubscribe extends Action
{
    protected $_subscriberFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Tigren\Dailydeal\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $subscriberId = $this->getRequest()->getParam('subscriber_id');
            $confirmCode = $this->getRequest()->getParam('confirm_code');
            $subscriber = $this->_subscriberFactory->create()->load($subscriberId);
            if ($subscriber->getId() && $subscriber->getConfirmCode() == $confirmCode) {
                $subscriber->delete();
                $this->messageManager->addSuccess(__('You have unsubscribed successfully!'));
            } else {
                $this->messageManager->addError(__('There was a problem when unsubscribing!'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was a problem when unsubscribing. Please try again.'));
        } finally {
            return $resultRedirect->setPath('dailydeal');
        }

    }
}
