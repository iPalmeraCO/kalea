<?php
namespace Inchoo\Helloworld\Block;
//require_once("../.../../../../vendor/guzzlehttp/guzzle/src/Client.php");
use Inchoo\Helloworld\Model\ApiKalea;

class Helloworld extends \Magento\Framework\View\Element\Template
{
  public $urlapi = "http://200.6.255.18:3002";  

    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }


      public function ping(){
        
        $request = new \Zend\Http\Request();  
        $request->setUri($this->urlapi.'/servicios/v1/ventas_online/generales/ping');
        // Performing a POST request
        $request->setMethod(\Zend\Http\Request::METHOD_POST);
        $params = new \Zend\Stdlib\Parameters(self::generarllave());
          $request->setQuery($params);
        $client = new \Zend\Http\Client();      
        //$client->setOptions(['strictredirects' => true]);
        $client->setRequest($request);
        $response = $client->send($request);

        return $response->getBody();    
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

     public function test_consulta_existencias($no_arti){    
        return self::getservice('/servicios/v1/ventas_online/consulta_items/consulta_existencias', array('no_arti'=>$no_arti));
      }

      public function getservice($url,$datos){
        $request = new \Zend\Http\Request();  
        $request->setUri($this->urlapi.$url);
        $request->setMethod(\Zend\Http\Request::METHOD_POST);
        $data = array_merge(self::generarllave(), $datos);        
        $params = new \Zend\Stdlib\Parameters($data);
        $request->setQuery($params);
        $client = new \Zend\Http\Client(); 
        $client->setRequest($request);
        $response = $client->send($request);        
        return $response->getBody();
      }

      public function consultar_pais($cod){        
        return self::getservice('/servicios/v1/ventas_online/generales/paises', array('pais'=>$cod));
      }

      public function consulta_departamentos($cod){
        return self::getservice('/servicios/v1/ventas_online/generales/departamentos', array('pais'=>$cod));
      }
      public function consulta_municipios($pais, $departamentos){
        return self::getservice('/servicios/v1/ventas_online/generales/municipios', array('pais'=>$pais, 'departamento'=>$departamentos));
      }

       public function consulta_distritos($pais, $departamentos, $municipio){
        return self::getservice('/servicios/v1/ventas_online/generales/municipios', array('pais'=>$pais, 'departamento'=>$departamentos, 'municipio'=>$municipio));
      }

      public function crear_cliente($cia, $cen, $nac, $codc, $inde, $tip, $tel, $nit, $con, $est){
         return self::getservice('/servicios/v1/ventas_online/clientes/crear',array('cliente'=>array('no_cia' =>$cia, 'centrod_crea'=>$cen , 'ind_nacional'=>$nac, 'ind_cod_cliente' => $codc, 'ind_empleado'=>$inde, 'tipo_identificacion'=>$tip, 'telefono'=>$tel, 'nit_factura'=>$nit, 'contacto'=>$con, 'estado_civil'=>$est)));
      }

       public function actualizar_cliente($cod, $tel, $con){
         return self::getservice('/servicios/v1/ventas_online/clientes/actualizar',array('cod_cliente' =>$cod,'cliente'=>array('telefono'=>$tel,'contacto'=>$con)));
      }

      public function consultar_cliente($cod){
         return self::getservice('/servicios/v1/ventas_online/clientes/ver',array('cod_cliente' =>$cod));
      }

      public function crear_cotizacion($cia,$cend,$cenf,$mov,$indcod,$codcli,$mon,$nomb,$br, $tc,$tcc,$tcv,$fec){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/crear',array('cotizacion'=>array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'tipo_movimiento'=>$mov,'ind_cod_cliente'=>$indcod,'cod_cliente'=>$codcli,'moneda'=>$mon,'nombre_cliente'=>$nomb,'total_bruto'=>$br,'tipo_cambio'=>$tc,'tipo_cambio_c'=>$tcc,'tipo_cambio_v'=>$tcv,'fecha'=>$fec)));
      }

       public function actualizar_cotizacion($cia,$cend,$cenf,$mov,$indcod,$mon,$nomb,$total, $fecha,$dire,$paise,$prove,$ce,$colent,$cllen,$casaen){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/actualizar',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov, 'cotizacion'=> array('ind_cod_cliente'=>$indcod,'moneda'=>$mon,'nombre_cliente'=>$nomb,'total'=>$total,'fecha'=>$fecha,'direccion_entrega'=>$dire,'pais_entrega'=>$paise,'provincia_entrega'=>$prove,'canton_entrega'=>$ce,'colonia_entrega'=>$colent,'calle_entrega'=>$cllen,'casa_entrega'=>$casaen)));
      }

      public function ver_cotizacion($cia,$cend,$cenf,$mov){
        return self::getservice('/servicios/v1/ventas_online/cotizaciones/ver',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
      }

       public function crear_detalle($cia,$cend,$cenf,$mov,$noarti,$can,$pre,$desc,$tn,$ed,$cd,$ins,$inse){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/crear_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'detalle'=>array('no_arti'=>$noarti,'cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
      }

        public function actualizar_detalle($cia,$cend,$cenf,$mov,$linea,$can,$pre,$desc,$tn,$ed,$cd,$ins,$inse){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/actualizar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea,'detalle'=>array('cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
      }

      public function ver_detalle($cia,$cend,$cenf,$mov,$linea){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/ver_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }

       public function eliminar_detalle($cia,$cend,$cenf,$mov,$linea){
         return self::getservice('/servicios/v1/ventas_online/cotizaciones/eliminar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }

       public function crear_pedido($cia,$cend,$cenf,$mov){
         return self::getservice('/servicios/v1/ventas_online/pedidos/crear',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
     }
       public function actualizar_pedido($cia,$cend,$cenf,$mov,$codcli,$mon,$ncli,$tb,$td,$tot,$tipc,$tipcc,$tipcv,$fec,$dire,$pae,$proe,$canen,$colen,$caen,$casen){
         return self::getservice('/servicios/v1/ventas_online/pedidos/actualizar',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'pedido'=>array('ind_cod_cliente'=>$codcli,'moneda'=>$mon,'nombre_cliente'=>$ncli,'total_bruto'=>$tb,'total_descuento'=>$td,'total'=>$tot,'tipo_cambio'=>$tipc,'tipo_cambio_c'=>$tipcc,'tipo_cambio_v'=>$tipcv,'fecha'=>$fec,'direccion_entrega'=>$dire,'pais_entrega'=>$pae,'provincia_entrega'=>$proe,'canton_entrega'=>$canen,'colonia_entrega'=>$colen,'calle_entrega'=>$caen,'casa_entrega'=>$casen)));
      }
        public function ver_pedido($cia,$cend,$cenf,$mov){
         return self::getservice('/servicios/v1/ventas_online/pedidos/ver',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
      }

       public function crear_detalle_pedido($cia,$cend,$cenf,$mov,$noarti,$can,$linea,$pre,$desc,$tn,$ed,$cd,$ins,$inse){
         return self::getservice('/servicios/v1/ventas_online/pedidos/crear_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'detalle'=>array('no_arti'=>$noarti,'cantidad'=>$can, 'linea'=>$linea, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
      }

       public function actualizar_detalle_pedido($cia,$cend,$cenf,$mov,$linea,$can,$pre,$desc,$tn,$ed,$cd,$ins,$inse){
         return self::getservice('/servicios/v1/ventas_online/pedidos/actualizar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea,'detalle'=>array('cantidad'=>$can, 'precio'=>$pre,'descuento'=>$desc,'total_neto'=>$tn,'entregadomicilio'=>$ed, 'cantidaddomicilio'=>$cd,'instalar'=>$ins,'inst_especial'=>$inse)));
      }

       public function ver_detalle_pedido($cia,$cend,$cenf,$mov,$linea){
         return self::getservice('/servicios/v1/ventas_online/pedidos/ver_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }

       public function eliminar_detalle_pedido($cia,$cend,$cenf,$mov,$linea){
         return self::getservice('/servicios/v1/ventas_online/pedidos/eliminar_detalle',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov,'linea'=>$linea));
      }
      public function factura($cia,$cend,$cenf,$mov){
         return self::getservice('/servicios/v1/ventas_online/pedidos/factura',array('no_cia'=>$cia,'centrod'=>$cend,'centrof'=>$cenf,'no_transa_mov'=>$mov));
      }
      public function getnumber(){
        return 22;
      }




}