<?php
namespace MageAfi\ProductImport\Controller\Index;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use MageAfi\ProductImport\Model\PostFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		RedirectFactory $resultRedirectFactory,
		SessionManagerInterface $session,
		PostFactory $postFactory,
		\Magento\Framework\View\Result\PageFactory $pageFactory
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->session = $session;
		$this->_postFactory = $postFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$this->session->start();
		$username = $this->session->getMageUser();
        $password = $this->session->getMagePassword();

        $post = $this->_postFactory->create()->getCollection();
		$post->addFieldToFilter('user', ['eq' => $username]);
		$post->addFieldToFilter('password', ['eq' => $password]);

		$resultRedirect = $this->resultRedirectFactory->create();

		echo $username;
		echo $password;
		echo count($post);
		if (count($post) > 0) {
			$resultRedirect->setPath('productimport/index/uploader');
			return $resultRedirect;
		}else{
			return $this->_pageFactory->create();
		}

		
	}
}