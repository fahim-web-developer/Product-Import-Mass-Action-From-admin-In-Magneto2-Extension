<?php
namespace MageAfi\ProductImport\Block;

use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Config;

class Index extends \Magento\Framework\View\Element\Template
{	
 
    protected $adminCategoryTree;
	protected $_storeManager;

	public function __construct(
		CollectionFactory $CollectionFactory,
		Context $context,
		StoreManagerInterface $storeManager,
        Config $eavConfig
	)
	{ 
		$this->collectionFactory = $CollectionFactory;
        $this->eavConfig = $eavConfig;
		parent::__construct($context);
	}
	/**
     * list attribute set
     *
     * @return AttributeSetInterface|null
     */
    public function getTree()
    {
         $categories = $this->collectionFactory->create()->addAttributeToSelect('*');

        return $categories; 
    }

    public function getattributeValue($att){
        $attribute = $this->eavConfig->getAttribute('catalog_product', $att);
        $options = $attribute->getSource()->getAllOptions();
        return $options;
    }
}