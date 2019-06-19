<?php

/**
 * Copyright 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AlanKent\CalculatorWebService\Model;

use AlanKent\CalculatorWebService\Api\CalculatorInterface;

/**
 * Defines the implementaiton class of the calculator service contract.
 */
class Calculator implements CalculatorInterface 
{
    public $errors = "esosi";
    public $catema = 0; //Categoria magento
    public $subcat = 0; //Subcategoria magento
    public $productaux = null;
    public $accion = "";
    public $accion2 = "";
    
    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $num1 Left hand operand.
     * @param int $num2 Right hand operand.
     * @return int The sum of the two values.
     */
    public function add($num1, $num2) {
        return $num1+$num2;        
    }

    protected $jsonHelper;

    public function __construct(\Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
    }

    public function get_products($valor1, $valor2, $llave, $products) {                        
        if (self::validate_security($valor1, $valor2, $llave)){
            $obj = json_decode($products);            
            if (self::validatejson($obj)){
                
                $id = time();
                if (self::insert_process($id)){
                    
                    
                    $result = self::process_products($obj,$id);
                    if ($result){
                        self::sendemail($id);
                        $result = array ("json valido # de proceso ".$id);
                        self::update_process($id);
                        self::reindexAll();
                    }                    
                }else{
                    $result = array("Error al insertar el proceso");
                }
            }else {
               $result = array ("json invalido") ;
            }    

        } else {
            $result = array('response'=>"Usted no esta autorizado para acceder a este recurso");
        }
        return $result;     
    }

    public function validate_security($valor1, $valor2, $llave){
        $llaveserver  =($valor1 * 245) + 231 . "VentasOnlineK" . $valor2;              
        $llaveserver = md5($llaveserver);        
        
        if ($llave == $llaveserver){
            return true;
        } else{
            return false;
        }
    }
   

    public function insert_process($id){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('process_ws_products');
        $sql = "Insert Into " . $tableName . " (id) Values ('".$id."')";     
        $connection->query($sql);  
        return true;
    }

    public function update_process($id){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('process_ws_products');
        $sql = "UPDATE " . $tableName . " SET estado=1 where id='".$id."'";     
        $connection->query($sql);  
        return true;
    }

    /*
    Validar json
    */
    public function validatejson($obj){
        $result = true;  
        if ($obj === null && json_last_error() !== JSON_ERROR_NONE) {
           $result =false;
        }
        return $result;
    }

    public function sendemail($idproccess){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
        $templateVars = [
                    'subject'    => 1, 
                    'telephone'  => $idproccess            
                ];
        $file = $idproccess.".txt";    
        $file2= file_get_contents("/var/www/html/logsws/".$file);    
        $data = $transport
            ->setTemplateIdentifier(11)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
            ->setTemplateVars($templateVars)
            ->setFrom(['name' => 'Notificación Proceso Web Service','email' => 'magento_back@pa-phone.com'])
            ->addAttachment(
                $file2,
                'text/plain',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $file
            )
            //->addTo(['julian.escobar@ipalmera.co'])
            ->addTo(['sistemas@kalea.com.gt'])
            ->getTransport();

        $data->sendMessage();
    }

   

    /*
    * Función para procesar productos
    *
    */
    public function process_products($products,$idproccess){

        $log = "";
        $conttotal = 0;
        $contok = 0;
        $conter = 0;

        foreach ($products as $pr) {
             if (self::validate_product($pr)){                                
                $insertar = self::ws_process_product($pr);              
                if ($insertar == 1){
                $log .= $pr->sku." ".$this->accion2." \r\n";    
                $contok = $contok + 1;
                } else {
                    $log .= " Error al ".$this->accion." ".$pr->sku." ".$insertar."\r\n";    
                    $conter = $conter + 1;
                }

             }else{
                $log .= "Error validación producto ".$pr->sku." ".$this->errors."\r\n";
                $conter = $conter + 1;
             }
                $this->productaux = null;
        }
        $sum = $contok + $conter;
        $log .= "Se procesaron ".$sum." Productos \r\n";
        $log .= "Ejecutados correctamente : ".$contok."\r\n";
        $log .= "Fallidos : ".$conter."\r\n";

        $myfile = fopen($_SERVER['DOCUMENT_ROOT']."/logsws/".$idproccess.".txt", "w") or die("Unable to open file!");        
        fwrite($myfile, $log);
        

        return true;
    }

    /*
    *
    *Función para validar producto
    */
    public function validate_product($pr){
        $r = true;
        $this->errors = "";
        $this->productaux = null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product');
        $exist = $product->getIdBySku($pr->sku);

        if ($exist){         
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $cacheManager = $objectManager->get('\Magento\Framework\App\CacheInterface');
            $cacheManager->clean('catalog_product_' . $exist); 
            //$this->productaux = $product->load($exist);                        
            $productR =      $objectManager ->create('\Magento\Catalog\Api\ProductRepositoryInterface');
            $product = $productR->get($pr->sku);
            $product->cleanCache();
            $this->productaux = $product;
            $this->productaux->setProductLinks([]);
            $this->productaux->save();
        
            
        } else {
            $this->productaux = null;
        }

        if ($pr->accion == "Nuevo" && $exist){
            $this->errors = "El sku ya se encuentra registrado ".$pr->sku;                  
            return false;
        } else if ($pr->accion == "Actualizar" && !$exist){
            $this->errors = "El sku que va actualizar no se encuentra registrado ".$pr->sku;                  
            return false;

        } else if ($pr->accion == "Eliminar" && !$exist){
            $this->errors = "El sku que va eliminar no se encuentra registrado ".$pr->sku;                 
            return false;
        }
        if (!strlen($pr->sku) || !strlen($pr->title)){
            $r= false;
            $this->errors = "Falta información requerida";
        }

        if (self::categoriaspr($pr->category,1) == -1) {
            $r= false;
            $this->errors = "Categoría no encontrada";
        }


          if ($pr->subcategory != null){
            if (self::categoriaspr($pr->subcategory,2) == -1) {
                $r= false;
                $this->errors = "Subcategoría no encontrada";
            }
        }else {
            $this->subcat = -1;
        }
        return $r;
    }

    public function ws_process_product ($pr){
         if ($pr->accion == "Nuevo"){
            $this->accion = "Crear";
            $this->accion2 = "Insertado";
            return self::ws_insert_product($pr);
        } else if ($pr->accion == "Actualizar"){
            $this->accion  = "Actualizar";
            $this->accion2 = "Actualizado";
            return self::ws_update_product($pr);
        } else if ($pr->accion == "Eliminar"){
            $this->accion = "Eliminar";
            $this->accion2 = "Eliminado";
            return self::ws_delete_product($pr);
        }
    }
    public function ws_insert_product ($pr){
           
        $precios = self::get_precio($pr->sell_price, $pr->precio_temporada,$pr->precio_increible);
        $precio = $precios[0];
        $precioespecial = $precios[1];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $product = $objectManager->create('\Magento\Catalog\Model\Product');
        $product->setSku($pr->sku); // Set your sku here
        $product->setStoreId(0);
        $product->setWebsiteIds(array(1));
        $product->setUrlKey($pr->title."-".$pr->sku);
        $product->setName($pr->title); // Name of Product
        $product->setAttributeSetId(4); // Attribute set id
        $product->setStatus(1); // Status on product enabled/ disabled 1/0
        $product->setProductHasWeight(1); // if product has weight
        $product->setWeight(10); // weight of product
        $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
        $product->setDescription($pr->body);
       
        $colork = self::setOrAddOptionAttribute('colork',$pr->color);
        $product->setData('colork', $colork); // Yes

        $estilo = self::setOrAddOptionAttribute('estilo',$pr->estilo);
        $product->setData('estilo', $estilo); 

        $acabado = self::setOrAddOptionAttribute('acabado',$pr->acabado);
        $product->setData('acabado', $acabado); 

        $material = self::setOrAddOptionAttribute('material',$pr->material);
        $product->setData('material', $material); 

        $medidas = self::setOrAddOptionAttribute('medidas',$pr->medidas);
        $product->setData('medidas', $medidas); 

        $tamanio = self::setOrAddOptionAttribute('medidas',$pr->tamanio);
        $product->setData('tamanio', $tamanio); 
        
        //Productos relacionados
        $linkDataAll = [];        
        $skuLinks = explode("||",$pr->related);
        $productObject = $objectManager->get('Magento\Catalog\Model\Product');      

        foreach($skuLinks as $skuLink) {
            //check first that the product exist
            $linkedProduct = $productObject->loadByAttribute("sku",$skuLink);
            if($linkedProduct) {
                /** @var  \Magento\Catalog\Api\Data\ProductLinkInterface $productLinks */
                $productLinks = $objectManager->create('Magento\Catalog\Api\Data\ProductLinkInterface');
                $linkData = $productLinks //Magento\Catalog\Api\Data\ProductLinkInterface
                    ->setSku($product->getSku())
                    ->setLinkedProductSku($skuLink)
                    ->setLinkType("related");
                $linkDataAll[] = $linkData;
            }

        }
        if($linkDataAll) {            
            $product->setProductLinks($linkDataAll);
        }
        //        

        $product->setTaxClassId(0); // Tax class id
        $product->setTypeId('simple'); // Type of product (simple/virtual/downloadable/configurable)
        $product->setPrice($precio);  // price of product
        $oferta = false;
         if ($precioespecial != -1){
        $product->setSpecialPrice($precioespecial); 
        $por = self::porcentajedescuento($precio,$precioespecial);
        if ( $por != 100){
            $descuento = self::setOrAddOptionAttribute('discount_percentage',self::porcentajedescuento($precio,$precioespecial));   
            $product->setData('discount_percentage',$descuento);
            $oferta = true;
        } else {
            $product->setData('discount_percentage',null);
        }
        
                
        }else {
            $product->setData('discount_percentage',null); 
        }

        $cats= self::get_categories($pr->lo_ultimo,$pr->producto_destacado, $this->catema,$this->subcat,$oferta);     
        $product->setCategoryIds($cats);

        $product->setStockData(
                     array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 99
                         )
                     );
        //$product->setNewsFromDate('11-11-2017'); // set the data from which product will be marked as new
        //$product->setNewsToDate('11-12-2017'); // set the data till when product will be marked as new
        //$product->setCountryOfManufacture('AL'); // Set country of manufacture        
        try {
            $product->save();
            return 1;             
        } catch (\Exception $e)  {            
            return -1;            
        }
    }

      public function ws_update_product ($pr){
        
        $precios = self::get_precio($pr->sell_price, $pr->precio_temporada,$pr->precio_increible);         
        $precio = $precios[0];
        $precioespecial = $precios[1];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();           

        $product = $this->productaux;        
        $product->setSku($pr->sku); // Set your sku here
        $product->setStoreId(0);
        $product->setWebsiteIds(array(1));
        $product->setUrlKey($pr->title."-".$pr->sku);
        $product->setName($pr->title); // Name of Product
        $product->setAttributeSetId(4); // Attribute set id
        $product->setStatus(1); // Status on product enabled/ disabled 1/0
        $product->setProductHasWeight(1); // if product has weight
        $product->setWeight(10); // weight of product
        $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
        $product->setDescription($pr->body);

        $colork = self::setOrAddOptionAttribute('colork',$pr->color);
        $product->setData('colork', $colork); // Yes

        $estilo = self::setOrAddOptionAttribute('estilo',$pr->estilo);
        $product->setData('estilo', $estilo); 

        $acabado = self::setOrAddOptionAttribute('acabado',$pr->acabado);
        $product->setData('acabado', $acabado); 

        $material = self::setOrAddOptionAttribute('material',$pr->material);
        $product->setData('material', $material); 

        $medidas = self::setOrAddOptionAttribute('medidas',$pr->medidas);
        $product->setData('medidas', $medidas); 

        $tamanio = self::setOrAddOptionAttribute('medidas',$pr->tamanio);
        $product->setData('tamanio', $tamanio); 
        
         //Productos relacionados
        $linkDataAll = [];    
        $product->setProductLinks($linkDataAll);    
        $skuLinks = explode("||",$pr->related);
        $productObject = $objectManager->get('Magento\Catalog\Model\Product');      

        foreach($skuLinks as $skuLink) {
            //check first that the product exist
            $linkedProduct = $productObject->loadByAttribute("sku",$skuLink);
            if($linkedProduct) {
                // @var  \Magento\Catalog\Api\Data\ProductLinkInterface $productLinks 
                $productLinks = $objectManager->create('Magento\Catalog\Api\Data\ProductLinkInterface');
                $linkData = $productLinks //Magento\Catalog\Api\Data\ProductLinkInterface
                    ->setSku($product->getSku())
                    ->setLinkedProductSku($skuLink)
                    ->setLinkType("related");
                $linkDataAll[] = $linkData;
            }

        }
        if($linkDataAll) {            
            $product->setProductLinks($linkDataAll);
        }
        //      
        
        
        
        $product->setTaxClassId(0); // Tax class id
        $product->setTypeId('simple'); // Type of product (simple/virtual/downloadable/configurable)
        $product->setPrice($precio);  // price of product
        $oferta = false;
        if ($precioespecial != -1){
        $product->setSpecialPrice($precioespecial); 
        $por = self::porcentajedescuento($precio,$precioespecial);
        if ( $por != 100){
            $descuento = self::setOrAddOptionAttribute('discount_percentage',self::porcentajedescuento($precio,$precioespecial));   
            $product->setData('discount_percentage',$descuento);
            $oferta = true;
        } else {
            $product->setData('discount_percentage',null);
        }
        
                
        }else {
            $product->setSpecialPrice(null); 
            $oferta = false;
            //Eliminar categoria de oferta  en el producto
            /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $CategoryLinkRepository = $objectManager->get('\Magento\Catalog\Model\CategoryLinkRepository'); 
            $CategoryLinkRepository->deleteByIds(24,$pr->sku);
            $product->setData('discount_percentage',null); */
        }

        $cats= self::get_categories($pr->lo_ultimo,$pr->producto_destacado, $this->catema,$this->subcat,$oferta);        
        $product->setCategoryIds($cats);
        $product->setStockData(
                     array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 99
                         )
                     );
        //$product->setNewsFromDate('11-11-2017'); // set the data from which product will be marked as new
        //$product->setNewsToDate('11-12-2017'); // set the data till when product will be marked as new
        //$product->setCountryOfManufacture('AL'); // Set country of manufacture        
        try {
            $product->save();
            return 1;             
        } catch (\Exception $e)  {
           return $e->getMessage();                    
        }
    }



    /* Porcentaje de descuento */

    public function porcentajedescuento($precio, $precioespecial){
    /*$product_price = $product->getPrice();
    $product_sp_price = $product->getSpecialPrice();
    $discount_percentage = ($product_sp_price/$product_price)*100;
    $product->setDiscountPercentage($discount_percentage);*/   
    $discount_percentage = ($precioespecial/$precio)*100;
    return round(100-$discount_percentage);
    //$product->setDiscountPercentage($discount_percentage);

    }

    /* Deshabilitar Producto */

    public function ws_delete_product($pr){
        $product = $this->productaux; 
        $product->setStatus(2);
        try {
            $product->save();
            return 1;             
        } catch (\Exception $e)  {            
            return -1;                      
        }
    }

    /*
    Retorna el array con las categorias 
    */
    public function get_categories($loultimo, $destacado, $categoria, $subcategoria, $oferta){
        
        $catss = array();

        if ($loultimo){
            array_push($catss, 40);
        }
        if ($destacado){
            array_push($catss, 23);
        }
        if ($oferta){
            array_push($catss, 24);
        }
        array_push($catss, $categoria);

        if ($subcategoria != -1){
        array_push($catss, $subcategoria);
        }

        return $catss;
    }

    /*
    Retorna el precio real de acuerdo a la validación 
    type 1- Precio 2-Get precio especial
    */
    public function get_precio($price ,$pricetemporada, $priceincre){
        $precio = 0;
        $precioespecial = -1;
        if ($price == $pricetemporada && $price != $pricetemporada){
            $precio = $priceincre;
        } else {
            $precio = $price;
        }

        if ($pricetemporada >= 0 && $pricetemporada < $price){
            $precioespecial = $pricetemporada;
        } else {
            $precioespecial = -1;
        }

        if ($precioespecial == 0.0 || $precioespecial == 0){
            $precioespecial = -1;
        }        
        return array(0=>$precio, 1=>$precioespecial); 

    }

    // Get category or subcategory name 
    public function categoriaspr($nombre,$type){
    if ($type == 1) {
        $this->catema = 0; 
     }else {
        $this->subcat = 0;
     }

      $cat = -1; 
      $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
      $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

    $categories = $categoryCollection->create()
                                     ->addAttributeToSelect('*')
                                     ->addAttributeToFilter('name', $nombre);
     
      if($categories->getSize()){    
         $cat = $categories->getFirstItem()->getId(); 
         if ($type == 1) {
         $this->catema = $cat;          
        }else {
         $this->subcat = $cat;    
        }
         
      }
     return $cat;
    }

    //
    public function setOrAddOptionAttribute($arg_attribute, $arg_value) {

        if ($arg_value == null){
            return $arg_value;
        }

        $return = "";     
        $instance = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Eav\Model\Config');
        $attribute = $instance->getAttribute('catalog_product', $arg_attribute);
        $options = $attribute->getSource()->getAllOptions();
        
        $value_exists = false;
        foreach ($options as $option) {
            if ($option['label'] == $arg_value) {
                $value_exists = true;
                break;
            }
        }

        //Si la opcion no existe se crea como nueva
        
        if (!$value_exists) {
            $attribute->setData('option', array(
                'value' => array(
                    'option' => array($arg_value, $arg_value)
                )
            ));
            $attribute->save();
        }
            // Now get the option value for this newly created attribute value
            $_product = \Magento\Framework\App\ObjectManager::getInstance()->get("\Magento\Catalog\Model\Product");
            $attr = $_product->getResource()->getAttribute($arg_attribute);
            if ($attr->usesSource()) {
                $option['value'] = $attr->getSource()->getOptionId($arg_value);
            }
           return $option['value'];
    }
    //

    public function reindexAll() {
    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
    $indexerFactory = $objectManager->get('Magento\Indexer\Model\IndexerFactory');
    $indexerIds = array(
        'catalog_category_product',
        'catalog_product_category',
        'catalog_product_price',
        'catalog_product_attribute',
        'cataloginventory_stock',
        'catalogrule_product',
        'catalogsearch_fulltext',
    );
    foreach ($indexerIds as $indexerId) {        
        $indexer = $indexerFactory->create();
        $indexer->load($indexerId);
        $indexer->reindexAll();
    }
}

}