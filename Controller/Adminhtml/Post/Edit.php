<?php
namespace MageAfi\ProductImport\Controller\Adminhtml\Post;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \MageAfi\ProductImport\Model\GridFactory
     */
    private $postFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \MageAfi\ProductImport\Model\GridFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \MageAfi\ProductImport\Model\PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->postFactory = $postFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postId = (int) $this->getRequest()->getParam('id');
        $postData = $this->postFactory->create();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        if ($postId) {
            $postData = $postData->load($postId);

            $postTitle = $postData->getUser();
            if (!$postData->getPostId()) {
                $this->messageManager->addError(__('Post no longer exist.'));
                $this->_redirect('productimport/post/');
                return;
            }
        }

        $this->coreRegistry->register('post_data', $postData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $postId ? __('Edit User ') . $postTitle : __('Add User');

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageAfi_ProductImport::edit');
    }
}
