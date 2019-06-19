<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$url = 'https://ws.shopshop.com.gt/CyberSource/VisaNET/GT/AuthorizationRequest/index.php?wsdl'; 
try {
$client = new SoapClient($url, array("trace" => 1, "exception" => 0));
} catch (Exception $ex) {
echo 'error conexion ' . $ex->getMessage(); die();
}

$datos['terminal'] = '99526388';
$datos['merchant'] = '020001011';
$datos['user'] = 'E48316433D476EA8CC61BD0F9BDE5CE50C09F0FDF72A15C9B395BBCF57CE7BCD';
$datos['pass'] = 'D11F57F931813DBF256BEA1C9463D90D6FE40A64F8ECC84A9D6D2093CB5EF6A5';
$datos['merchant_id'] = 'visanetgt_bimagua';
$datos['Token'] = 'illJUkUJKzVwJ7eRWQn1ljbeqwXRrARmkiP+fECvzZt4o56YaWYOe05r3GeDUMFRN8eOUdBY7osS/zxK+jqlzWlxOGJLrvs0QH5UPHSv0nqXAWF+7cSSxuZZ46VypYIls8a1rCvPi+1CAokMAgnp+mm5q+f9uGu7xYaBjhxPMbEtquFShEKbuZ8rzSy6ENjVtarHUJIptsjltA3FDimbSIyn9YKNvUeWm5o1ixpyHi91Amuewi18rN6iEDGjiDaEsLx1TsvAgITkDxA3fGdaNotLdEpTfXufBRR03fRjiNqSHLqPsJMGGpuOvAbAjAOxQbkQtixlBo6jJSjnWpWRwA==';
$datos['CybsEpay'] = 'epay';
$datos['expirationMonth'] = '12';
$datos['expirationYear'] = '19';
$datos['accountNumber'] = '4111111111111111';
$datos['cvv'] = '123';
$datos['monto'] = '1805';
$datos['vc'] = 'VC06';
$datos['firstName'] = 'Nombre de prueba';
$datos['lastName'] = 'Apellido de prueba';
$datos['merchantReferenceCode'] = '0003';
$datos['currency'] = 'GTQ';
$datos['field10'] = 'WEB';
$datos['country'] = 'GT';
$datos['city'] = 'Guatemala';
$datos['state'] = 'Guatemala';
$datos['street'] = '1era calle a 12-05 zona 10 oakland';
$datos['postalCode'] = '01010';
$datos['email'] = 'pruebas@shopshop.com.gt';



try {
$response = $client->Authorization($datos);
} catch (Exception $ex) {
echo 'error consumno ' . $ex->getMessage(); die();
}
echo "<strong>REQUEST:\n </strong>" . htmlentities($client->__getLastRequest()) . "\n"; echo "<br>"; echo "<br>";

$doc = new DOMDocument;
$doc->loadXML($client->__getLastResponse());
echo $doc->getElementsByTagName('responseCode')->item(0)->nodeValue;
echo $doc->getElementsByTagName('authorizationCode')->item(0)->nodeValue;
echo $doc->getElementsByTagName('referenceNumber')->item(0)->nodeValue;

echo "<strong> Response:\n </strong>" . htmlentities($client->__getLastResponse()) . "\n"; die(); 


?>