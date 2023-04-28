<?php

namespace  MageAfi\ProductImport\Controller\Index;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use MageAfi\ProductImport\Model\ProductimportFactory;
use Magento\Framework\File\Csv;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\Marketplace\Model\ProductFactory as WebkulProductFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Catalog\Model\Product;
use MageAfi\ProductImport\Helper\Data;

class UploadFile extends \Magento\Framework\App\Action\Action
{
    
      
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_attributeSetCollection;

    protected $storeManager;
    
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;

    protected $_sourceItemsSaveInterface;
    protected $_sourceItemFactory;

    /**
     * @param Context $context
     * @param ImageFileUploader $imageFileUploader
     */
    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        Csv $csv,
        ProductFactory $product,
        Config $eavConfig,
        CollectionFactory $attributeSetCollection,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeFactory $eavAttributeFactory,
        StoreManagerInterface $storeManagerInterface,
        ProductRepository $productRepository,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
        WebkulProductFactory $mpProductFactory,
        DateTime $date,
        Data $helperData,
        MarketplaceHelperData $marketplaceHelperData,
        Product $productCollection,
        ProductimportFactory $productImportFactory
        
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->productImportFactory = $productImportFactory;
        $this->csv = $csv;
        $this->_product = $product;
        $this->_productRepository = $productRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->_attributeSetCollection = $attributeSetCollection;
        $this->_productCollection = $productCollection;
        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->_sourceItemFactory = $sourceItemFactory;

        $this->_mpProductFactory = $mpProductFactory;
        $this->_marketplaceHelperData = $marketplaceHelperData;

        $this->_date = $date;
        $this->helperData = $helperData;

        parent::__construct($context);
        
    }

    /**
     * Image upload action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {        
        $file = $this->getRequest()->getFiles('upload_file');
        $this->import($file);

        try {
            $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'upload_file']); 
            $uploaderFactory->setAllowedExtensions(['csv']); // you can add more extension which need
            $fileAdapter = $this->adapterFactory->create();
            $uploaderFactory->setAllowRenameFiles(true);
            $uploaderFactory->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('productfile');
            $result = $uploaderFactory->save($destinationPath);
            
            // print_r($result);
            if (!$result) {
                throw new LocalizedException(
                    __('File cannot be saved to path: $1', $destinationPath)
                );
            }

        }  catch (\Exception $e) {
            $this->messageManager->addError(__('File not Uplaoded, Please try Agrain'));
        }
    }


    public function import($file){
        if (!isset($file['tmp_name']))
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));

        $csvData = $this->csv->getData($file['tmp_name']);

        $exist = "";
        $i = 1;
        foreach ($csvData as $row => $data) {

            if ($row > 0){

                $sku = $data[0];

                if ($sku != "" && $data[1] && $data[2] && $data[3] && $data[4] && $data[7] && $data[11] && $data[17] && $data[18]) {
                    try {
                        
                        // Insert product *****************                 
                        try {
                            $exist = $this->_productRepository->get($sku);                    
                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $exist = false;
                        }

                        if ($exist== false) {
                            $product = $this->_product->create();
                            $product->setSku($sku);
                            $product->setCreatedAt(strtotime('now'));
                        } else {
                            $product = $this->_product->create()->load($exist->getEntityId());
                            $product->setEntityId($exist->getEntityId());
                            $product->setUpdatedAt(strtotime('now'));
                        }
                        
                        $product->setWebsiteIds(array(1));
                        $product->setTypeId('simple');
                        $product->setStatus(1);
                        $product->setTaxClassId(2);

                        $AttributeSetId = $this->getAttrSetId($data[1]);
                        $product->setAttributeSetId($AttributeSetId);

                        $name = $data[2];
                        $product->setName($name);
                        $urlKey = $this->getUrl($name,$sku);
                        $product->setUrlKey($urlKey);
                        $product->setMetaTitle($name);
                        $product->setMetaKeyword($name);
                        

                        $categoryIds = explode("|", trim($data[3]));
 
                        if (count($categoryIds) > 1) {
                            $product->setCategoryIds($categoryIds);
                        }else{
                            $product->setCategoryIds($data[3]);
                        }


                        $product->setVisibility($data[4]);
                        $product->setDescription($data[5]);
                        $product->setShortDescription($data[6]);

                        // price
                        $product->setPrice($data[7]);
                        $product->setSpecialPrice($data[8]);
                        
                        // $product->setSpecialFromDate('06-1-2022'); 
                        // special price from (MM-DD-YYYY)
                        // $product->setSpecialToDate('06-30-2022');

                        if ($data[9]) {
                            $product_benefits = $this->getAttCode('product_benefits',$data[9]);
                            if (!empty($product_benefits)) {
                                $product->setProductBenefits($product_benefits);
                            }else{
                                $product_benefits = $this->helperData->createOrGetId('product_benefits',$data[9]);
                                $product->setProductBenefits($product_benefits);
                            }
                        }

                        if (strtolower($data[10]) == 'yes') {
                            $product->setFeatured(1);
                        }else{
                            $product->setFeatured(0);
                        }


                        // qty
                        $qty = $data[11];
                        $min_sale_qty = $data[12];
                        $max_sale_qty = $data[13];
                        $is_in_stock = $data[14];

                        $product->setStockData(
                            array(
                            'use_config_manage_stock' => 1, 
                            'manage_stock' => 1, // manage stock
                            'min_sale_qty' => $min_sale_qty, // Shopping Cart Minimum Qty Allowed 
                            'max_sale_qty' => $max_sale_qty, // Shopping Cart Maximum Qty Allowed
                            'is_in_stock' => $is_in_stock, // Stock Availability of product
                            'qty' => $qty
                            )
                        );


                        $country_of_manufacture = $this->getAttCode('country_of_manufacture',$data[15]);

                        if ($data[15]) {
                            if (!empty($country_of_manufacture)) {
                                $product->setCountryOfManufacture($country_of_manufacture);
                            }
                            else{
                                $product->setCountryOfManufacture("IN");
                            }
                        }else{
                            $product->setCountryOfManufacture("IN");
                        }



                        $product->setWeight($data[16]);

 
                        $product->setMetaDescription($data[20]);

                        if ($data[21]) {
                            $manufacturer = $this->getAttCode('manufacturer',$data[21]);
                            if (!empty($manufacturer)) {
                                $product->setManufacturer($manufacturer);
                            }else{
                                $manufacturer = $this->helperData->createOrGetId('manufacturer',$data[21]);
                                $product->setManufacturer($manufacturer);
                            }
                        }


                        if ($data[22]) {
                            $color = $this->getAttCode('color',$data[22]);
                            if (!empty($color)) {
                                $product->setColor($color);
                            }else{
                                $color = $this->helperData->createOrGetId('color',$data[22]);
                                $product->setColor($color);
                            }
                        }

                        if (strtolower($data[23]) == 'yes') {
                            $product->setSwFeatured(1);
                        }else{
                            $product->setSwFeatured(0);
                        }

                        if ($data[24]) {
                            $hair_type = $this->getAttCode('hair_type',$data[24]);
                            if (!empty($hair_type)) {
                                $product->setHairType($hair_type);
                            }else{
                                $hair_type = $this->helperData->createOrGetId('hair_type',$data[24]);
                                $product->setHairType($hair_type);
                            }
                        }

                        if ($data[25]){
                            $gender = $this->getAttCode('gender',$data[25]);
                            if (!empty($gender)) {
                                $product->setGender($gender);
                            }else{
                                $gender = $this->helperData->createOrGetId('gender',$data[25]);
                                $product->setGender($gender);
                            }
                        }

                        $skintype = explode("|", trim($data[26]));
                        $skin_types = array();
                        foreach ($skintype as $skintype_value) {
                            $skin_types[] = $this->getAttCode('skin_type',trim($skintype_value));
                        }
                        $skin_type = implode(",",$skin_types);
                        $product->setData('skin_type', $skin_type);


                        if ($data[27]) {
                            $formulation = $this->getAttCode('formulation',$data[27]);
                            if (!empty($formulation)) {
                                $product->setFormulation($formulation);
                            }else{
                                $formulation = $this->helperData->createOrGetId('formulation',$data[27]);
                                $product->setFormulation($formulation);
                            }
                        }

                        $skinconcerns = explode("|", trim($data[28]));
                        $skin_concerns = array();
                        foreach ($skinconcerns as $skinconcerns_value) {
                            $skin_concerns[] = $this->getAttCode('skin_concern',trim($skinconcerns_value));
                        }
                        $skin_concern = implode(",",$skin_concerns);
                        $product->setData('skin_concern', $skin_concern);



                        $preferencess = explode("|", trim($data[29]));
                        $preferences = array();
                        foreach ($preferencess as $preference_value) {
                            $preferences[] = $this->getAttCode('preference',trim($preference_value));
                        }
                        $preference = implode(",",$preferences);
                        $product->setData('preference', $preference);


                        if ($data[30]) {
                            $finish = $this->getAttCode('finish',$data[30]);
                            if (!empty($finish)) {
                                $product->setFinish($finish);
                            }else{
                                $finish = $this->helperData->createOrGetId('finish',$data[30]);
                                $product->setFinish($finish);
                            }
                        }

                        $product->setProductBenefitsNew($data[31]);
 

                        if ($data[32]) {
                            $skin_tone = $this->getAttCode('skin_tone',$data[32]);
                            if (!empty($skin_tone)) {
                                $product->setSkinTone($skin_tone);
                            }else{
                                $skin_tone = $this->helperData->createOrGetId('skin_tone',$data[32]);
                                $product->setSkinTone($skin_tone);
                            }
                        }

                        if ($data[33]) {
                            $occasion = $this->getAttCode('occasion',$data[33]);
                            if (!empty($occasion)) {
                                $product->setOccasion($occasion);
                            }else{
                                $occasion = $this->helperData->createOrGetId('occasion',$data[33]);
                                $product->setOccasion($occasion);
                            }
                        }

                        if ($data[34]) {
                            $warranty = $this->getAttCode('warranty',$data[34]);
                            if (!empty($warranty)) {
                                $product->setWarranty($warranty);
                            }else{
                                $warranty = $this->helperData->createOrGetId('warranty',$data[34]);
                                $product->setWarranty($warranty);
                            }
                        }

                        if ($data[35]) {
                            $coverage = $this->getAttCode('coverage',$data[35]);
                            if (!empty($coverage)) {
                                $product->setCoverage($coverage);
                            }else{
                                $coverage = $this->helperData->createOrGetId('coverage',$data[35]);
                                $product->setCoverage($coverage);
                            }
                        }

                        $product->setHsnCode($data[36]);


                        $hairconcerns = explode("|", trim($data[37]));
                        $hair_concerns = array();
                        foreach ($hairconcerns as $hair_concern_value) {
                            $hair_concerns[] = $this->getAttCode('hair_concern',trim($hair_concern_value));
                        }
                        $hair_concern = implode(",",$hair_concerns);
                        $product->setData('hair_concern', $hair_concern);
                         

                        if (strtolower($data[38]) == 'yes') {
                            $product->setIsItHeatSensitive(1);
                        }else{
                            $product->setIsItHeatSensitive(0);
                        }

                        if (strtolower($data[39]) == 'yes') {
                            $product->setExpiry(1);
                        }else{
                            $product->setExpiry(0);
                        }

                        $ingredientss = explode("|", trim($data[40]));
                        $ingredients = array();
                        foreach ($ingredientss as $ingredient_value) {
                            $ingredients[] = $this->getAttCode('ingredient',trim($ingredient_value));
                        }
                        $ingredient = implode(",",$ingredients);
                        $product->setData('ingredient', $ingredient);

                        $product->setDirectionForUse($data[41]);
                        $product->setMaterialComposition($data[42]);
                        $product->setHeight($data[43]);
                        $product->setWidth($data[44]);
                        $product->setLength($data[45]);
                        $product->setSize($data[46]);


                        if ($data[47]) {
                            $stay = $this->getAttCode('stay',$data[47]);

                            if (!empty($stay)) {
                                $product->setstay($stay);
                            }else{
                                $stay = $this->helperData->createOrGetId('stay',$data[47]);
                                $product->setstay($stay);
                            }
                        }

                        if ($data[48]) {
                            $spf = $this->getAttCode('spf',$data[48]);
                            if (!empty($spf)) {
                                $product->setspf($spf);
                            }else{
                                $spf = $this->helperData->createOrGetId('spf',$data[48]);
                                $product->setspf($spf);
                            }
                        }

                        $fragrancefamilys = explode("|", trim($data[49]));
                        $fragrance_familys = array();
                        foreach ($fragrancefamilys as $fragrancefamilys_value) {
                            $fragrance_familys[] = $this->getAttCode('fragrance_family',trim($fragrancefamilys_value));
                        }
                        $fragrance_family = implode(",",$fragrance_familys);
                        $product->setData('fragrance_family', $fragrance_family);


                        $ageranges = explode("|", trim($data[50]));
                        $age_ranges = array();
                        foreach ($ageranges as $ageranges_value) {
                            $age_ranges[] = $this->getAttCode('age_range',trim($ageranges_value));
                        }
                        $age_range = implode(",",$age_ranges);
                        $product->setData('age_range', $age_range);


                        if ($data[51]) {
                            $shelf_life = $this->getAttCode('shelf_life',$data[51]);
                            if (!empty($shelf_life)) {
                                $product->setShelfLife($shelf_life);
                            }else{
                                $shelf_life = $this->helperData->createOrGetId('shelf_life',$data[51]);
                                $product->setShelfLife($shelf_life);
                            }
                        }

                        $product->setManufactureDetails($data[52]);
                        $product->setEanCode($data[53]);
                        

                        $unit_of_measurement_of_pack_si = $this->getAttCode('unit_of_measurement_of_pack_si',$data[54]);
                        if (!empty($unit_of_measurement_of_pack_si)) {

                            $product->setUnitOfMeasurementOfPackSi($unit_of_measurement_of_pack_si);
                        }else{
                            $unit_of_measurement_of_pack_si = $this->helperData->createOrGetId('unit_of_measurement_of_pack_si',$data[54]);
                            $product->setUnitOfMeasurementOfPackSi($unit_of_measurement_of_pack_si);
                        }

                        $product->setProductCertification($data[55]);

                        if (strtolower($data[56]) == 'yes') {
                            $product->setReturnable(1);
                        }else{
                            $product->setReturnable(0);
                        }

                        if (strtolower($data[57]) == 'yes') {
                            $product->setRefundable(1);
                        }else{
                            $product->setRefundable(0);
                        }
                        if (strtolower($data[58]) == 'yes') {
                            $product->setExchangeable(1);
                        }else{
                            $product->setExchangeable(0);
                        }


                        $product->setBenefits($data[59]);
                        $product->setIngredientList($data[60]);

                        //create the sourceItem using the factory

                        if ($data[61]) {
                            $sourceItem = $this->setSourceItem($sku,'delhi-warehouse',$data[61]);
                        }else{
                            $sourceItem = $this->setSourceItem($sku,'delhi-warehouse',$qty);
                        }
                        if ($data[62]) {
                            $sourceItem = $this->setSourceItem($sku,'mumbai-wherehouse',$data[62]);
                        }else{
                            $sourceItem = $this->setSourceItem($sku,'mumbai-wherehouse',$qty);
                        }
                        if ($data[63]) {
                            $sourceItem = $this->setSourceItem($sku,'NORTHGM',$data[63]);
                        }else{
                            $sourceItem = $this->setSourceItem($sku,'NORTHGM',$qty);
                        }


                        // img
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                        $mediaPath = $mediaDirectory->getAbsolutePath();
                        $image_directory = $mediaPath;

                        $thumbnail = $data[18];
                        // Main Image
                        if ($thumbnail) {
                            $image_directory = $mediaPath . DS . 'catalog/productimport' . DS . $thumbnail;
                            if (file_exists($image_directory)) {
                                $product->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                                    ->addImageToMediaGallery($image_directory, array('thumbnail'), false, false);//assigning image, thumb and small image to media gallery
                            } 
                            else {
                                $image_directory = $mediaPath . 'catalog/productimport' . DS . 'comingsoon.jpg';
                                $product->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                                    ->addImageToMediaGallery($image_directory, array('thumbnail'), false, false);
                            }
                        }

                        $mainImage = $data[17];
                        // Main Image
                        if ($mainImage) {
                            $image_directory = $mediaPath . DS . 'catalog/productimport' . DS . $mainImage;
                            if (file_exists($image_directory)) {
                                $product->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                                    ->addImageToMediaGallery($image_directory, array('image','small_image'), false, false);//assigning image, thumb and small image to media gallery
                            } 
                            else {
                                $image_directory = $mediaPath . 'catalog/productimport' . DS . 'comingsoon.jpg';
                                $product->setMediaGallery(array('images' => array(), 'values' => array()))//media gallery initialization
                                    ->addImageToMediaGallery($image_directory, array('image','small_image'), false, false);
                            }
                        }

                       


                        $product->save();


                        // additional images
                        if ($data[19] != '') {
                            $addImages = explode(",", trim($data[19]));
                            foreach ($addImages as $additional_image) {


                                $image_directory = $mediaPath .DS.'catalog/productimport'.DS. trim($additional_image);
 

                                if (file_exists($image_directory)) {
                                    $product->addImageToMediaGallery($image_directory, null, false, false);

                                } 
                                else {
                                    $image_directory = $mediaPath . 'catalog/productimport' . DS . 'comingsoon.jpg';
                                    $product->addImageToMediaGallery($image_directory, null, false, false);
                                }

                                $product->save();

                            }
                        }

                       
                        

                        $seller = $this->setSellerOnProduct($sku,$data[64]);
                        
                     } catch (\Exception $e) {

                        $writer = new \Zend\Log\Writer\Stream(BP . '/pub/media/data_problem.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info($sku);

                    }
                }else{

                    $writer = new \Zend\Log\Writer\Stream(BP . '/pub/media/empty_field.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info("Mandatory field empty line no: ".$i);
                }
                
            }
            $i++;
        }

        if (count($csvData) > 0) {

            $storeUrl = $this->storeManagerInterface->getStore()->getBaseUrl();

            echo "<h4 style='text-align:center;'>Successfully Update<br><div class='form_btn'><a  href='".$storeUrl."productimport'>Back</a></div><div class='error_btn'><a target='_blank' href='".$storeUrl."pub/media/empty_field.log'>View Empty Field</a><br><a target='_blank' href='".$storeUrl."pub/media/data_problem.log'>View Error Log</a></div></h4><style>body h4 {margin-top: 5%;background: black;display: block;width: 30%;margin: auto;padding: 20px;border-radius: 10px;color: #fff;}.form_btn a {border: 1px solid #fff;color: #fff;width: 62%;margin: 23px auto;display: block;padding: 10px;font-size: 16px;text-decoration: none;border-radius: 5px;}.form_btn a:hover {background: #fff;color: #000;}.error_btn a {width: 50%;color: #ffbe00;}.error_btn {display: flex;flex-wrap: wrap;text-align: center;}</style>";
        }
        die();
    }

    public function getAttCode($name,$value){

        $values = $this->eavConfig->getAttribute('catalog_product', $name)->getSource()->getOptionId($value);

        return $values;
    }

    public function getUrl($productName,$sku){

        if (str_word_count($productName)>1) {
            $url = preg_replace('#[^0-9a-z]+#i', '-', $productName);
            $lastCharTitle = substr($productName, -1);
            $lastUrlChar = substr($url, -1);
            if ($lastUrlChar == "-" && $lastCharTitle != "-"){
                $url = substr($url, 0, strlen($url) - 1);
            }
        }else{
            $url = $productName;
        }
        $urlKey = strtolower($url);
        $urlKey = $urlKey . '-' . $sku;

        return $urlKey;
    }

    public function setSourceItem($sku,$code,$qty){

       $sourceItem = $this->_sourceItemFactory->create();
       $sourceItem->setSourceCode($code);
       $sourceItem->setSku($sku);
       $sourceItem->setQuantity($qty);
       $sourceItem->setStatus(1);

       $this->_sourceItemsSaveInterface->execute([$sourceItem]);

       return $sourceItem;

    }

    public function setSellerOnProduct($sku,$sellerId){

        $helper = $this->_marketplaceHelperData;

        $productId = $this->_productCollection->getIdBySku($sku);
        $collection = $this->_mpProductFactory->create()->load($productId);
        $collection->setMageproductId($productId);
        $collection->setSellerId($sellerId);
        $collection->setStatus(1);

        if ($helper->getIsProductEditApproval()) {
            $collection->setAdminPendingNotification(2);
        }

        $collection->setIsApproved(1);
        $collection->setUpdatedAt($this->_date->gmtDate());
        $collection->save();

        return $collection;
    }

    public function getAttrSetId($attrSetName){

        $attributeSet = $this->_attributeSetCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('attribute_set_name',$attrSetName);

        $attributeSetId = 0;

        foreach($attributeSet as $attr):
            $attributeSetId = $attr->getAttributeSetId();
        endforeach;

        return $attributeSetId;
    }

}

