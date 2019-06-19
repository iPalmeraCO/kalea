<?php 

if (isset($_REQUEST['tarjeta']) && isset($_REQUEST['nombre']) && isset($_REQUEST['cvv']) && isset($_REQUEST['vc']) && isset($_REQUEST['vence'])) {
	$url = 'https://em.grupotelematika.com/VolGTM3.1/volpago.php?wsdl'; 
try {
$client = new SoapClient($url, array("trace" => 1, "exception" => 0));
} catch (Exception $ex) {
echo 'error conexion ' . $ex->getMessage(); die();
}

$datos['MERCHANT_ID'] = 'visanetgt_kalea';
$datos['auto'] = '100001';
$datos['cliente'] = ' 1057 ';
$datos['company'] = 'KALEA';
$datos['remota'] = '192.168.100.201';
$datos['productSKU'] = '1009';
$datos['nombre'] = 'java';
$datos['apellido'] = 'velasquez';
$datos['email'] = 'j.velasquez@cybercompgt.net';
$datos['usuario'] = 'java';
$datos['test'] = '1';
$datos['tarjeta'] = '4111111111111111';
$datos['cvv'] = '123';
$datos['vc'] = 'VC06';
$datos['expirationYear'] = '19';
$datos['expirationMonth'] = '05';
$datos['productCode'] = '9012';
$datos['productName'] = 'Camiosolas de colores';
$datos['vence'] = '1905';
$datos['monto'] = '7550';
$datos['quantity'] = '1';
$datos['id'] = '1001';
$datos['currency'] = 'GTQ';
$datos['nombre2'] = 'el mismo';
$datos['apellido2'] = 'el mismo';
$datos['email2'] = 'elmismo@gmail.com';
$datos['street1'] = 'guatemala zona 10';
$datos['street2'] = 'oficinas geminis 10 3-55';
$datos['postalCode'] = '01009';
$datos['telefono'] = '40466869';
$datos['country'] = 'GT';
$datos['state'] = 'GT';
$datos['city'] = 'GT';
$datos['vta_orden'] = '1';
$datos['vta_bin'] = '4111';
$datos['vta_metodo'] = 'VISA';
$datos['vta_telefono'] = '?';
$datos['vta_tienda'] = '?';
$datos['vta_envio'] = '?';
$datos['vta_clienteCompra'] = '?';
$datos['vta_contrasena'] = '?';
$datos['vta_numeroCupon'] = '?';
$datos['vta_canal'] = '?';
$datos['vta_factura'] = '?';
$datos['vta_diasEntrega'] = '?';
$datos['vta_diasCompraInicial'] = '?';
$datos['vta_diasCompra'] = '?';
$datos['vta_diasRegistro'] = '?';
$datos['vta_ticket'] = '?';
$datos['vta_documentoComprador'] = '?';



try {
$response = $client->ViTmProceso($datos);
} catch (Exception $ex) {
echo 'error consumno ' . $ex->getMessage(); die();
}
//echo "<strong>REQUEST:\n </strong>" . htmlentities($client->__getLastRequest()) . "\n"; echo "<br>"; echo "<br>";
//echo "<strong> Response:\n </strong>" . htmlentities($client->__getLastResponse()) . "\n"; die(); 
$doc = new DOMDocument();
        $doc->loadXML($client->__getLastResponse());
        echo $doc->getElementsByTagName('codigo')->item(0)->nodeValue;
        echo $doc->getElementsByTagName('mensaje')->item(0)->nodeValue;

} else {
	echo "ERROR";
}



?>