<?php
namespace MageAfi\ProductImport\Model;
class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'mageafi_productimport_post';

	protected $_cacheTag = 'mageafi_productimport_post';

	protected $_eventPrefix = 'mageafi_productimport_post';

	protected function _construct()
	{
		$this->_init('MageAfi\ProductImport\Model\ResourceModel\Post');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}