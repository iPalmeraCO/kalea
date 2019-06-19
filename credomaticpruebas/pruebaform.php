<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<h2>Formulario 1 </h2>
<Form name="CredomaticPost" method="post" action="https://credomatic.compassmerchantsolutions.com/api/transact.php"/>
<Input type="text" name="type" value="capture"/>
<Input type="text" name="key_id" value="k49338953"/>
<Input type="text" name="hash" value="28519d58218c0a43a300b538c7303836" />
<Input type="text" name="time" value="1366839938"/>
<Input type="text" name="transactionid" value="415263"/>
<Input type="text" name="amount" value="1.00"/>
<Input type="submit" value="Enviar Transacción"/>
</Form>
<?php 
$orderid = "CredomaticTest";
$amount =  "1.00";
$time   =  time();
$key    =  "k49338953";
$hash   = md5("$orderid|$amount|$time|$key");


$orderid2 = "test";
$amount2= "1.00";
$time2 = "1279302634"; //Este valor equivale a Viernes, 16 Julio 2010 17:50:34 GMT 
$key2= "k49338953";
echo MD5 ("test|1.00|1279302634|23232332222222222222222222222222")."<br>";
$hash2= md5("$orderid2|$amount2|$time2|$key2");
echo $hash2;


?>

	<h2>Formulario 2 </h2>
	<Form name="CredomaticPost" method="post" action="https://credomatic.compassmerchantsolutions.com/api/transact.php" />
<Input type="text" name="type" value="auth"/>
<Input type="text" name="key_id" value="<?= $key2; ?>"/>
<Input type="text" name="hash" value="<?= $hash2; ?>" />
<Input type="text" name="time" value="<?= $time2; ?>"/>
<Input type="text" name="amount" value="<?= $amount2; ?>"/>
<Input type="text" name="orderid" value="<?= $orderid2; ?>"/>
<Input type="text" name="processor_id" value=""/>
<Input type="text" name="ccnumber" value="4111111111111111"/>
<Input type="text" name="ccexp" value="1220"/>
<Input type="text" name="cvv" value="123"/>
<Input type="text" name="avs" value="12 Calle San Jose"/>
<Input type="submit" value="Enviar Transacción"/>
</Form>

</body>
</html>
