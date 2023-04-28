<?php
namespace MageAfi\ProductImport\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'post_id';
	protected $_eventPrefix = 'mageafi_productimport_post_collection';
	protected $_eventObject = 'post_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('MageAfi\ProductImport\Model\Post', 'MageAfi\ProductImport\Model\ResourceModel\Post');
	}

}
