<?php

namespace MageAfi\ProductImport\Model\ResourceModel\Productimport;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'mageafi_productimport_collection';
    protected $_eventObject = 'productimport_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageAfi\ProductImport\Model\Productimport', 'MageAfi\ProductImport\Model\ResourceModel\Productimport');
    }
}
