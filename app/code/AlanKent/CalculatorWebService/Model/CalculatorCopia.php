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
                    self::sendemail($id);
                    //$result = array ("json valido # de proceso ".$id);
                    $result = self::process_products($obj,$id);
                    if ($result){
                        $result = array ("json valido # de proceso ".$id);
                        self::update_process($id);
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

        $data = $transport
            ->setTemplateIdentifier(11)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
            ->setTemplateVars($templateVars)
            ->setFrom(['name' => 'Notificación Proceso Web Service','email' => 'magento_back@pa-phone.com'])
            ->addTo(['julian.escobar@ipalmera.co'])
            //->addTo(['sistemas@kalea.com.gt'])
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
                $insertar = self::ws_insert_product($pr);              
                if ($insertar == 1){
                $log .= " OK ".$pr->sku."\r\n";    
                $contok = $contok + 1;
                } else {
                    $log .= " Error al insertar ".$pr->sku." ".$insertar."\r\n";    
                    $conter = $conter + 1;
                }

             }else{
                $log .= "Error validación producto ".$pr->sku." ".$this->errors."\r\n";
                $conter = $conter + 1;
             }
        }
        $sum = $contok + $conter;
        $log .= "Se procesaron ".$sum." Productos \r\n";
        $log .= "Insertados correctamente ".$contok."\r\n";
        $log .= "Fallidos ".$conter."\r\n";

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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product');

        if($product->getIdBySku($pr->sku)) {            
            $this->errors = "El sku ya se encuentra registrado ".$pr->sku;  
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

        if (self::categoriaspr($pr->subcategory,2) == -1) {
            $r= false;
            $this->errors = "Subcategoría no encontrada";
        }
        return $r;
    }

    public function ws_insert_product ($pr){
        $cats= self::get_categories($pr->lo_ultimo,$pr->producto_destacado, $this->catema,$this->subcat);        
        $precio = self::get_precio($pr->sell_price, $pr->precio_temporada,$pr->precio_increible);

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
        $product->setData('colork', $pr->color); // Yes
        $product->setData('estilo', $pr->estilo); 
        $product->setData('acabado', $pr->acabado); 
        $product->setData('material', $pr->material); 
        $product->setData('medidas', $pr->medidas); 
        $product->setData('tamanio', $pr->tamanio); 
        
        
        $product->setCategoryIds($cats);
        $product->setTaxClassId(0); // Tax class id
        $product->setTypeId('simple'); // Type of product (simple/virtual/downloadable/configurable)
        $product->setPrice($precio);  // price of product
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
            return $e;            
        }
    }

    /*
    Retorna el array con las categorias 
    */
    public function get_categories($loultimo, $destacado, $categoria, $subcategoria){
        $catss = array();

        if ($loultimo){
            array_push($catss, 40);
        }
        if ($destacado){
            array_push($catss, 23);
        }
        array_push($catss, $categoria);
        array_push($catss, $subcategoria);
      
        return $catss;
    }

    /*
    Retorna el precio real de acuerdo a la validación 
    */
    public function get_precio($price ,$pricetemporada, $priceincre){
        $precio = 0;
        if ($price == $pricetemporada){
            $precio = $priceincre;
        } else {
            $precio = $price;
        }
        return $precio; 

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
}