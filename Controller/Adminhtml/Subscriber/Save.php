<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Subscriber;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    protected $_date;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->_date = $date;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $subscriber = $this->_objectManager->create('Tigren\Dailydeal\Model\Subscriber');
            $id = $this->getRequest()->getParam('subscriber_id');
            if ($id) {
                $subscriber->load($id);
                if ($id != $subscriber->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong subscriber is specified.'));
                }
            }

            $subscriber->setData($data);

            $this->_eventManager->dispatch(
                'dailydeal_subscriber_prepare_save', ['subscriber' => $subscriber, 'request' => $this->getRequest()]
            );

            try {
                $subscriber->save();
                $this->messageManager->addSuccess(__('You saved this Subscriber.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit',
                        ['subscriber_id' => $subscriber->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the subscriber.'));
            }

            $this->_getSession()->setFormData($data);
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['subscriber_id' => $id, '_current' => true]);
            } else {
                return $resultRedirect->setPath('*/*/new', ['_current' => true]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Dailydeal::save');
    }

}
