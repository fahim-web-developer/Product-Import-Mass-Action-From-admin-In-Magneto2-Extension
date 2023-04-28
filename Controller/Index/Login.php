<?php
namespace MageAfi\ProductImport\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Session\SessionManagerInterface;

class Login extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\MageAfi\ProductImport\Model\PostFactory $postFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		SessionManagerInterface $session,
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
	)
	{
		$this->_postFactory = $postFactory;
		$this->_messageManager = $messageManager;
		$this->session = $session;
		$this->resultRedirectFactory = $resultRedirectFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$username = $this->getRequest()->getParam('username'); 
		$password = $this->getRequest()->getParam('password');

		$post = $this->_postFactory->create()->getCollection();
		$post->addFieldToFilter('user', ['eq' => $username]);
		$post->addFieldToFilter('password', ['eq' => $password]);


		$resultRedirect = $this->resultRedirectFactory->create();
        
       
		if (count($post) > 0) {
			$this->session->start();
			$this->session->setMageUser($username);
        	$this->session->setMagePassword($password);

			$this->_messageManager->addSuccessMessage('Login Success');
			$resultRedirect->setPath('productimport/index/uploader');
			return $resultRedirect;
		}else{
			$this->_messageManager->addErrorMessage('Type Currect User and Password');
        	$resultRedirect->setPath('productimport');
			return $resultRedirect;
		}
	}
}