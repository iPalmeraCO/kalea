<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'classcredomatic.php';		
$keyid = "k49338953"; //identificador de la Llave Generada por el panel del banco
$key = "abcLDALJKNdwejn3241234231414"; //llave generada por el panel del banco
$cc_num="378282246310005"; //Numero de tarjeta de credito (Este es un ejemplo)
$cc_cvv="1234"; //Codigo de Verificacion de la tarjeta
$cc_exp="1218"; //Fecha de vencimiento en formato MMdd
$cc_fname="Luffy"; //primer nombre del titular de la tarjeta
$cc_lname="Monkey"; //Apellido del titular de la tarjeta
$montototal = 345.50; //Monto de la transaccion
$tipo = "sale"; //tipo de transaccion el Banco especifica que otras transacciones se pueden hacer
$idregistro = 001;
//Creamos el Objeto con los parametros necesarios
$pago = new credomatic($idregistro, $tipo, $keyid, $key, $cc_num, $cc_cvv, $cc_exp, $cc_fname,$cc_lname, $montototal);

//Llamamos el metodo procesarpago que se encarga de realizar la transaccion
$respuesta = $pago->procesarpago();

if(!isset($respuesta["response"])){
	$respuesta = array("response"=>0, "responsetext">="No se pudo contactar la entidad Bancaria", "response_code"=>0);
}


if($respuesta["response"]==1 && $respuesta["responsetext"]=="SUCCESS" && $respuesta["response_code"]==100){
	$mensaje = "transaccion Exitosa";
}
else{
	$resp = isset($respuesta["responsetext"]) ? $respuesta["responsetext"] : "Error Desconocido";
	$mensaje = "Error al realizar el pago, Consulte su entidad Bancaria: $resp";

}

echo "$mensaje";?>