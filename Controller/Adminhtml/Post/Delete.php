<?php

namespace MageAfi\ProductImport\Controller\Adminhtml\Post;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class Delete extends \Magento\Backend\App\Action
{

    /**
     * Massactions filter.?_
     * @var Filter
     */
    protected $_filter;
 
    /**
     * @param Context           $context
     * @param Filter            $filter
     */
    public function __construct(
        Context $context,
        Filter $filter
    ) {
 
        $this->_filter = $filter;
        parent::__construct($context);
    }

    public function execute()
    {
        $user = $this->getRequest()->getParam('id');
 
        $model = $this->_objectManager->create('MageAfi\ProductImport\Model\Post');
        $model = $model->setId($user);

        try {
            $model->delete();
            $this->messageManager->addSuccess(
                __('User Delete Successfully')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('productimport/post/index');
    }
}
