<?php

namespace MageAfi\ProductImport\Model\ResourceModel;

class Productimport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $_idFieldName = 'id';

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('mageafi_productimport', 'id');
    }
}
