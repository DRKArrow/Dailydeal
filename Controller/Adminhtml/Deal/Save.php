<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Dailydeal\Controller\Adminhtml\Deal;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    protected $_date;
    protected $jsHelper;
    protected $_productFactory;
    protected $_helper;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Backend\Helper\Js $jsHelper,
        \Tigren\Dailydeal\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_date = $date;
        $this->jsHelper = $jsHelper;
        $this->_productFactory = $productFactory;
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
            $deal = $this->_objectManager->create('Tigren\Dailydeal\Model\Deal');
            $id = $this->getRequest()->getParam('deal_id');
            if ($id) {
                $deal->load($id);
                if ($id != $deal->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong deal is specified'));
                }
            }
            //Process time
            $localeDate = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $data['start_time'] = $localeDate->date($data['start_time'])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $data['end_time'] = $localeDate->date($data['end_time'])->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

            //Set progress_status
            $nowTime = time();
            $startTime = strtotime($data['start_time']);
            $endTime = strtotime($data['end_time']);
            $progressStatus = '';
            if ($startTime > $nowTime) {
                $progressStatus = 'coming';
            } else {
                if ($startTime <= $nowTime && $nowTime <= $endTime) {
                    $progressStatus = 'running';
                } else {
                    if ($endTime < $nowTime) {
                        $progressStatus = 'ended';
                    }
                }
            }
            $data['progress_status'] = $progressStatus;

            //Change logic to add,remove product each deal
            $productIds = array_keys($this->jsHelper->decodeGridSerializedInput($data['product_ids']));
            $data['product_ids'] = [];
            foreach($productIds as $productId) {
                $product = $this->_helper->getProductById($productId);
                if($product->getTypeId() == 'configurable') {
                    $data['product_ids'][] = $productId;
                }
            }
            $selectedProductIds = $data['product_ids'];
            foreach ($selectedProductIds as $idProduct) {
                $products = $this->_helper->getProductById($idProduct);
                if ($products->getTypeId() == 'configurable') {
                    $childIds = $products->getTypeInstance()->getChildrenIds($products->getId());
                    if (!empty($childIds)) {
                        foreach ($childIds[0] as $childId) {
                            if (!in_array($childId, $data['product_ids'])) {
                                $data['product_ids'][] = $childId;
                            }
                        }
                    }
                }
            }

            $deal->setData($data);
            $this->_eventManager->dispatch(
                'dailydeal_deal_prepare_save', ['deal' => $deal, 'request' => $this->getRequest()]
            );

            try {
                $deal->save();

                $this->messageManager->addSuccess(__('You saved this Deal'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['deal_id' => $deal->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the deal'));
            }

            $this->_getSession()->setFormData($data);
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['deal_id' => $id, '_current' => true]);
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
