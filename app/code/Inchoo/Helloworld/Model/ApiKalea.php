<?php
    namespace Inchoo\Helloworld\Model;

    

    class ApiKalea 
    {
        public $urlapi = "http://200.6.255.18:3002";
        public $cia = "01";
        public $centrod_crea = "01";
        public $ind_nacional = "S";
        public $ind_cod_cliente = "E";
        public $ind_empleado = "N";
        public $tipo_identificacion = 1;        
        public $centrod = "09";
        //public $centrof = "VI01";
        public $centrof = "VL02";
        public $tipo_movimiento = "PRO";
        public $moneda =  "P";
        public $tipo_cambio = "7.4";
        public $entregadomicilio = "D";
        public $instalar = "N";
        public $inst_especial = "D";


       /**
         * Initialize resource model
         *
         * @return void
         */
        public function __construct()
        {
            
        }

        public  static function getexis(){
            return 999;
        }

        function generateRandomString($length = 5) {
          $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
              $randomString .= $characters[rand(0, $charactersLength - 1)];
          }
          return $randomString;
         }

         
         function generarllave(){
          $valor1 = rand(5, 15);
          $valor2 = self::generateRandomString();
          $llave  =($valor1 * 245) + 231 . "VentasOnlineK" . $valor2;      
          $llave = md5($llave);
          return array('valor1' => $valor1,'valor2' => $valor2,'llave' => $llave);
         }

         public function consulta_existencias($no_arti){    
            return self::getservice('/servicios/v1/ventas_online/consulta_items/consulta_existencias', array('no_arti'=>$no_arti));
          }

          public function getservice($url,$datos){
           
            $response = -1;
            try {
            $request = new \Zend\Http\Request();  
            $request->setUri($this->urlapi.$url);
            $request->setMethod(\Zend\Http\Request::METHOD_POST);
            $data = array_merge(self::generarllave(), $datos);        
            $params = new \Zend\Stdlib\Parameters($data);
            $request->setQuery($params);
            $client = new \Zend\Http\Client();
            //$client->setConfig(array("timeout"=>120));
            $client->setOptions(array(
                'maxredirects' => 0,
                'timeout'      => 240
            ));
            $client->setRequest($request);
            $response = $client->send($request);        
            $response = $response->getBody();   
            $response =  json_decode($response,true); 
            }  catch(\Exception $e){
              return $e;
            }

            return $response;
            
          }

           public function crear_cliente($tel, $cel, $nit, $con, $fnac, $email, $sexo, $est){           
           $cen = $this->centrod_crea;
           $est  = self::getestadocivil($est);
           $sexo = self::getsexo($est);

          return self::getservice('/servicios/v1/ventas_online/clientes/crear',array('cliente'=>array('centrod_crea'=>$cen , 'telefono'=>$tel, 'telefono_celular' => $cel, 'telefono_oficina'=>'', 'nit_factura'=>$nit, 'contacto'=>$con, 'nombre'=>$con, 'nombre_factura' => $con, 'f_nacimiento'=>$fnac, 'email1'=>$email, 'sexo' => $sexo, 'estado_civil'=>$est)));
          }

          public function getestadocivil($int){
            $return = "O";
            if ($int == 1){
              $return = "S";
            }  else if ($int == 2){
              $return = "C";
            }
            return $return;
          }

          public function getsexo($int){
            $return = "F";
            if ($int == 1){
              $return = "M";
            }
            return $return;
          }

          public function actualizar_cliente($cod, $tel, $con){
           return self::getservice('/servicios/v1/ventas_online/clientes/actualizar',array('cod_cliente' =>$cod,'cliente'=>array('telefono'=>$tel,'contacto'=>$con)));
          }

          public function consultar_cliente($cod, $nit){
             return self::getservice('/servicios/v1/ventas_online/clientes/ver',array('cod_cliente' =>$cod, 'nit_factura'=>$nit));
          }

          /* Cotizaciones */
          public function crear_cotizacion($codcli,$nomb,$br){
            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;
            $mov    = $this->tipo_movimiento;
            $indcod = $this->ind_cod_cliente; 
            $mon    = $this->moneda;
            $tc     = $this->tipo_cambio;
            $tcc    = $this->tipo_cambio;
            $tcv    = $this->tipo_cambio;
            $fec    = $fecha = date("d-m-Y");
             return self::getservice('/servicios/v1/ventas_online/cotizaciones/crear',array('cotizacion'=>array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'tipo_movimiento'=>$mov,'ind_cod_cliente'=>$indcod,'cod_cliente'=>$codcli,'moneda'=>$mon,'nombre_cliente'=>$nomb,'total_bruto'=>$br,'tipo_cambio'=>$tc,'tipo_cambio_c'=>$tcc,'tipo_cambio_v'=>$tcv,'fecha'=>$fec)));
          }

          /* Crear Detalle de cotización en bd */
          /* 
              Numero de cotizacion $mov
              Sku articulo         $no_arti
              Cantidad             $can
              Precio               $precio 
              Número de de detalle $mov_detalle
          */
          public function crear_detallebd($linea,$noarti,$can,$pre,$mov){
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('detalle_cotizacion');
              $sql = "Insert Into " . $tableName . " (linea, no_arti, cantidad, precio, no_transa_mov) Values ('".$linea."','".$noarti."','".$can."','".$pre."','".$mov."')";     
              $connection->query($sql);  
              return array("respuesta" => true);
              //
          }

          public function actualizar_detallebd($id,$can,$pre){
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('detalle_cotizacion');
              $sql = "UPDATE " . $tableName . " SET  cantidad = '".$can."', precio ='".$pre."' WHERE id = '".$id."'";     
              $connection->query($sql);   
              return array("respuesta" => true);             
              //
          }

          public function eliminardetallebd($id){
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('detalle_cotizacion');
              $sql = "DELETE FROM  " . $tableName . " WHERE id = '".$id."'";    
              $connection->query($sql);  
              return array("respuesta" => true);
              //
          }

          public function actualizar_cotizacion($nomov, $nomb, $total, $dire,$paise,$prove,$ce,$colent,$cllen,$casaen){

            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;                        
            $mon    = $this->moneda;
            $indcod = $this->ind_cod_cliente; 
            $tc     = $this->tipo_cambio;
            $tcc    = $this->tipo_cambio;
            $tcv    = $this->tipo_cambio;
            $fecha  = date("d-m-Y");

            $prove = self::formatzerokalea($prove);
            $ce    = self::formatzerokalea($ce);

            /*self::enviarfacturaerror(print_r(array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$nomov, 'cotizacion'=> array('ind_cod_cliente'=>$indcod,'moneda'=>$mon,'nombre_cliente'=>$nomb,'total'=>$total,'fecha'=>$fecha,'direccion_entrega'=>$dire,'pais_entrega'=>$paise,'provincia_entrega'=>$prove,'canton_entrega'=>$ce,'colonia_entrega'=>$colent,'calle_entrega'=>$cllen,'casa_entrega'=>$casaen)),true),'julian.escobar@ipalmera.co');*/

             return self::getservice('/servicios/v1/ventas_online/cotizaciones/actualizar',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$nomov, 'cotizacion'=> array('ind_cod_cliente'=>$indcod,'moneda'=>$mon,'nombre_cliente'=>$nomb,'total'=>$total,'fecha'=>$fecha,'direccion_entrega'=>$dire,'pais_entrega'=>$paise,'provincia_entrega'=>$prove,'canton_entrega'=>$ce,'colonia_entrega'=>$colent,'calle_entrega'=>$cllen,'casa_entrega'=>$casaen)));
          }

          public function ver_cotizacion($cia,$cend,$cenf,$mov){
            return self::getservice('/servicios/v1/ventas_online/cotizaciones/ver',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
          }

          public function formatzerokalea($valor){
            if (strlen($valor) == 1){
              $newvalor="00".$valor;
            }
            else {
              $newvalor="0".$valor;
            }
            return $newvalor;
          }

          /* Detalles de cotización */

          public function crear_detalle($mov,$noarti,$can,$pre,$desc,$tn,$cd){
            
            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;
            $ed     = $this->entregadomicilio;
            $ins    = $this->instalar;
            $inse   = $this->inst_especial;           

            $id = self::consultar_detallebd($mov , $noarti); //Consultar si articulo ya existe para actualizarlo

            if ($id == -1){
              $success = self::getservice('/servicios/v1/ventas_online/cotizaciones/crear_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'detalle'=>array('no_arti'=>$noarti,'cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));

              if ($success["respuesta"]){
                return self::crear_detallebd($success['descripcion']['detalles'][0]['linea'],$noarti,$can,$pre,$mov);              
              } else {
                return $success;
              }
            } else {
              $id = $id[0];
              $cantidad = intval($can) + intval($id["cantidad"]);              
              return self::actualizar_detalle($id["id"], $mov, $id["linea"],$cantidad, $pre, 0, $pre, $cantidad);

            }

          }

          public function actualizar_detalle($id, $mov,$linea,$can,$pre,$desc,$tn,$cd){

            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;
            $ed     = $this->entregadomicilio;
            $ins    = $this->instalar;
            $inse   = $this->inst_especial;           


           $success = self::getservice('/servicios/v1/ventas_online/cotizaciones/actualizar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea,'detalle'=>array('cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));

           if ($success["respuesta"]){
              return self::actualizar_detallebd($id,$can,$pre);
           }  else {
             return array("respuesta" => false);
           }


          }

          public function consultar_detallebd($mov, $articulo){

              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('detalle_cotizacion');
              $sql = "SELECT id, linea, cantidad from " . $tableName . " WHERE  no_transa_mov = '".$mov."' AND no_arti = '".$articulo."'";     
              $consulta = $connection->fetchAll($sql);
              if (count($consulta))
              {
                return $consulta;
              } else{
                return -1;
              }

          }

        public function eliminar_detalle($id,$mov,$linea){

          $cia    = $this->cia;
          $cend   = $this->centrod;
          $cenf   = $this->centrof;
          $success = self::getservice('/servicios/v1/ventas_online/cotizaciones/eliminar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));

           if ($success["respuesta"]){

              $detpedi = self::consultar_detalle_pedido($mov,$linea);
              if ($detpedi["respuesta"]){
                self::eliminar_detalle_pedido($mov,$linea);
              }
              return self::eliminardetallebd($id);
            } else {
              return array("respuesta" => false);
            }
        }

        public function crear_pedido($mov){

          $cia    = $this->cia;
          $cend   = $this->centrod;
          $cenf   = $this->centrof;

         return self::getservice('/servicios/v1/ventas_online/pedidos/crear',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
        }

      public function consultar_pedido($mov){
          $cia    = $this->cia;
          $cend   = $this->centrod;
          $cenf   = $this->centrof;

         return self::getservice('/servicios/v1/ventas_online/pedidos/ver',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
      }

        public function crear_detalle_pedido($nomov,$noarti,$can,$linea,$pre,$desc,$tn,$cd){

            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;
            $ed     = $this->entregadomicilio;
            $ins    = $this->instalar;
            $inse   = $this->inst_especial; 

         return self::getservice('/servicios/v1/ventas_online/pedidos/crear_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$nomov,'detalle'=>array('no_arti'=>$noarti,'cantidad'=>$can, 'linea'=>$linea, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
        }

      public function consultar_detalle_pedido($mov,$linea){

         $cia    = $this->cia;
         $cend   = $this->centrod;
         $cenf   = $this->centrof;

         return self::getservice('/servicios/v1/ventas_online/pedidos/ver_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }

       public function eliminar_detalle_pedido($mov,$linea){

         $cia    = $this->cia;
         $cend   = $this->centrod;
         $cenf   = $this->centrof;

         return self::getservice('/servicios/v1/ventas_online/pedidos/eliminar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }

     /* public function actualizar_detalle_pedido($mov,$linea,$can,$pre,$desc,$tn,$ed,$cd,$ins,$inse){

            $cia    = $this->cia;
            $cend   = $this->centrod;
            $cenf   = $this->centrof;
            $ed     = $this->entregadomicilio;
            $ins    = $this->instalar;
            $inse   = $this->inst_especial; 

         return self::getservice('/servicios/v1/ventas_online/pedidos/actualizar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea,'detalle'=>array('cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
      }*/


        /* Crear el detalle de pedido de acuerdo  a lo que este en el carrito*/
        public function crear_detallespedidocotizacion($nomov){
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('detalle_cotizacion');
              $sql = "SELECT id, linea, no_arti, cantidad, precio from " . $tableName . " WHERE  no_transa_mov = '".$nomov."'";     
              $consulta = $connection->fetchAll($sql);
              $return = -1;  
              $logs = "";            
              if (count($consulta))
              {
                foreach ($consulta as $row) {
                  $existencia = self::consultar_detalle_pedido($nomov,$row["linea"]);

                  if ($existencia["respuesta"]){ //Comprobar si existe
                        $eli= self::eliminar_detalle_pedido($nomov,$row["linea"]);
                        if (!$eli["respuesta"]){
                          $return = -1;
                          break;
                        }
                  } 
                  
                  $det= self::crear_detalle_pedido($nomov, $row["no_arti"], $row["cantidad"], $row["linea"], $row["precio"],0,$row["precio"],$row["cantidad"]);                      
                  
                    if (!$det["respuesta"]){
                      //$return= $nomov. "- ". $row["no_arti"]. "- ". $row["cantidad"]. "- ". $row["linea"]. "- ". $row["precio"]. "- 0 - ".$row["precio"]. "- ".$row["cantidad"];
                      $return = -1;                       
                      break;
                    } else {
                      $return = 1;
                    }
                 
                }
              } 
              return $return;
        }

        public function crear_factura($mov, $email,$nombrecliente){

         $cia    = $this->cia;
         $cend   = $this->centrod;
         $cenf   = $this->centrof;

         $serv = self::getservice('/servicios/v1/ventas_online/pedidos/factura',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
         if ($serv["respuesta"]){ //Si genera factura
            $fact = $serv["descripcion"]["factura"];
             if (!file_put_contents('/var/www/html/fact/'.$mov.'.pdf', base64_decode($fact))){
              self::enviarfacturaerror($mov,"sistemas@kalea.com.gt");
             }else {
              //Se creo el archivo se envia archivo
              self::enviarfactura($mov,$email,$nombrecliente);
             }

         }else {
          self::enviarfacturaerror($mov,"sistemas@kalea.com.gt");
          //self::enviarfacturaerror(print_r($serv["descripcion"],true),"julian.escobar@ipalmera.co");
         }
      }

        /* Funciones en metodos de pago */


      public function actualizar_cotizacioncontroller($no_transa_mov, $codcliente, $cedula, $telefono, $nombre, $apellido, $monto, $region, $ciudad, $direccion, $productos){
        
        
        $nombres = $nombre." ".$apellido;               
        $success = $this->actualizar_cotizacion($no_transa_mov, $nombres, $monto, $direccion, "01" , $region,$ciudad, 0,0,0);

        if ($success["respuesta"]){
            $response = $success;
            //$response = $no_transa_mov." - " $codcliente." - " $nombres." - " $monto." - " $direccion." - " "Guatemala" ." - " $region." - "$ciudad." - ";
        }else {
            $response = $success;
        }  
        
        return $response;
       }


        public function crearpedidocontroller ($no_transa_mov){        
          $ped = $this->consultar_pedido($no_transa_mov);
          if (!$ped["respuesta"]){            
                  $this->crear_pedido($no_transa_mov);                       
          }                      
          return $this->crear_detallespedidocotizacion($no_transa_mov);        
        }

        /* Limpiar código de pedido */


        public function limpiarno_transa_mov(){
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
          $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
          $customerSession = $objectManager->get('Magento\Customer\Model\Session');
          $customer = $customerFactory->load($customerSession->getCustomer()->getId()); 
          $customerData = $customer->getDataModel();
          $customerData->setCustomAttribute('cotizacion', "");
          $customer->updateData($customerData);     
          $customer->save();   
        }


        public function getdepartamento($region, $nombre){
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('eadesign_romcity');              
              $sql = "SELECT idcanton FROM " . $tableName . " WHERE city ='".$nombre."' and region_id=".$region;  
              
              $consulta = $connection->fetchAll($sql);
              if (count($consulta))
              {
                return $consulta[0]["idcanton"];
              } else{
                return -1;
              }

        }



        /* */
        public function enviarfactura($mov,$email,$nombrecliente){
          try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
            $templateVars = [
                        'subject'    => 1 ,
                        'nombre'     => $nombrecliente                                 
                    ];
            $file = $mov.".pdf";    
            $file2= file_get_contents("/var/www/html/fact/".$file);    
            $data = $transport
                ->setTemplateIdentifier(18)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
                ->setTemplateVars($templateVars)
                ->setFrom(['name' => 'Kalea','email' => 'magento_back@pa-phone.com'])
                ->addAttachment(
                    $file2,
                    'text/plain',
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $file
                )
                //->addTo(['julian.escobar@ipalmera.co'])
                ->addTo([$email])
                ->addTo(['sistemas@kalea.com.gt'])
                ->getTransport();

            $data->sendMessage();
          } catch(\Exception $e){
            $this->enviarfacturaerror($mov, 'sistemas@kalea.com.gt');
          }
        }


        public function enviarfacturaerror($mov,$email){
            
            try{
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
              $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
              $templateVars = [
                          'subject'    => 1 ,
                          'pedido'     => $mov                                 
                      ];            
              $data = $transport
                  ->setTemplateIdentifier(19)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
                  ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
                  ->setTemplateVars($templateVars)
                  ->setFrom(['name' => 'Kalea','email' => 'magento_back@pa-phone.com'])                            
                  ->addTo([$email])                
                  ->getTransport();

              $data->sendMessage();
            } catch(\Exception $e){
            $this->enviarfacturaerror($mov, 'sistemas@kalea.com.gt');
          }
        }

          public function enviarcopiatransaccion($response, $monto, $email, $codusuario, $nombusuario){
            $monto = number_format($monto, 0, ".", ",");
            $doc = new \DOMDocument;
            $doc->loadXML($response);
            date_default_timezone_set('America/Guatemala');
            $magentoDate = date('m/d/Y h:i:s a');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
            $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
            $templateVars = [
                    'subject'    => 1, 
                    'fecha'  => $magentoDate,
                    'codusuario' => $codusuario,
                    'nombusuario' => $nombusuario,
                    'monto'  => $monto,
                    'nreferencia' => $doc->getElementsByTagName('referenceNumber')->item(0)->nodeValue,
                    'nautorizacion' => $doc->getElementsByTagName('authorizationCode')->item(0)->nodeValue,                                 
                ];          
            $data = $transport
                ->setTemplateIdentifier(20)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
                ->setTemplateVars($templateVars)
                ->setFrom(['name' => 'Notificación Pago Éxitoso','email' => 'magento_back@pa-phone.com'])                       
                ->addTo(['ventasenlinea@kalea.com.gt'])                 
                ->addTo(['sistemas@kalea.com.gt'])                 
                ->getTransport();

            $data->sendMessage();
        }    



        /*   */
    }