<?php
namespace MageAfi\ProductImport\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_post';
		$this->_blockGroup = 'MageAfi_ProductImport';
		$this->_headerText = __('Users');
		$this->_addButtonLabel = __('Create New User');
		parent::_construct();
	}
}
