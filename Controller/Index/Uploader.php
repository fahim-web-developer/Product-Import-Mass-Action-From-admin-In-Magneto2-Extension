<?php
namespace MageAfi\ProductImport\Controller\Index;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Session\SessionManagerInterface;
use MageAfi\ProductImport\Model\PostFactory;

class Uploader extends \Magento\Framework\App\Action\Action
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

        $empty_field = fopen(BP . '/pub/media/empty_field.log', 'w');
        $data_problem = fopen(BP . '/pub/media/data_problem.log', 'w');
        fclose($empty_field);
        fclose($data_problem);
        $post = $this->_postFactory->create()->getCollection();
		$post->addFieldToFilter('user', ['eq' => $username]);
		$post->addFieldToFilter('password', ['eq' => $password]);

		$resultRedirect = $this->resultRedirectFactory->create();
 
		if (count($post) > 0) {
			return $this->_pageFactory->create();
		}else{
			$resultRedirect->setPath('productimport');
			return $resultRedirect;
		}
	}
}