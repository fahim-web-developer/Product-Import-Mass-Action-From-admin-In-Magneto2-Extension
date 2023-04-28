<?php

namespace MageAfi\ProductImport\Controller\Adminhtml\Post;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Save extends Action
{

    protected $postFactory;
    protected $_filesystem;
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \MageAfi\ProductImport\Model\PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->postFactory = $postFactory;
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
       

        if (!$data) {
            $this->_redirect('productimport/post/edit');
            return;
        }
        try {
            $postData = $this->postFactory->create();

            $postData->setData($data);

            $postData->save();

            $this->messageManager->addSuccess(__('Post has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('productimport/post/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageAfi_ProductImport::save');
    }
}
