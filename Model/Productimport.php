<?php

namespace MageAfi\ProductImport\Model;

class Productimport extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    const CACHE_TAG = 'mageafi_productimport';

    protected $_cacheTag = 'mageafi_productimport';
    protected $_eventPrefix = 'mageafi_productimport';

    protected function _construct()
    {
        $this->_init('MageAfi\ProductImport\Model\ResourceModel\ProductImport');
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
