<h1>TEST</h1>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*$files = glob("/var/www/html/resources/AA0859/" . "*.{jpg,png,gif}", GLOB_BRACE);
asort($files);
$lastElement = end($files);
$arrayimgas = array("caption"=>"","full"=>$lastElement, "isMain"=>true, "position"=>1, "thumb"=>$lastElement, "type"=>"image", "videoUrl"=>false);
array_pop($files);
$cont=2;
foreach ($files as $img) {	
	echo basename($img);
	die();
	$arrayim = array("caption"=>"","full"=>$img, "isMain"=>false, "position"=>$cont, "thumb"=>$img, "type"=>"image", "videoUrl"=>false);
	array_push($arrayimgas, $arrayim);
	$cont = $cont + 1; 
}

//print_r($arrayimgas);
print_r(json_encode($arrayimgas));*/

$fact= "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b2RpbmcvV2luQW5zaUVuY29kaW5nPj4KZW5kb2JqCjYgMCBvYmoKPDwvS2lkc1sxIDAgUl0vVHlwZS9QYWdlcy9Db3VudCAxL0lUWFQoMi4xLjcpPj4KZW5kb2JqCjggMCBvYmoKPDwvTmFtZXNbKEpSX1BBR0VfQU5DSE9SXzBfMSkgNyAwIFJdPj4KZW5kb2JqCjkgMCBvYmoKPDwvRGVzdHMgOCAwIFI+PgplbmRvYmoKMTAgMCBvYmoKPDwvTmFtZXMgOSAwIFIvVHlwZS9DYXRhbG9nL1BhZ2VzIDYgMCBSL1ZpZXdlclByZWZlcmVuY2VzPDwvUHJpbnRTY2FsaW5nL0FwcERlZmF1bHQ+Pj4+CmVuZG9iagoxMSAwIG9iago8PC9Nb2REYXRlKEQ6MjAxOTAzMjUyMTQ0MzktMDYnMDAnKS9DcmVhdG9yKEphc3BlclJlcG9ydHMgTGlicmFyeSB2ZXJzaW9uIDYuMy4wKS9DcmVhdGlvbkRhdGUoRDoyMDE5MDMyNTIxNDQzOS0wNicwMCcpL1Byb2R1Y2VyKGlUZXh0IDIuMS43IGJ5IDFUM1hUKT4+CmVuZG9iagp4cmVmCjAgMTIKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDE5NDMzIDAwMDAwIG4gCjAwMDAwMTk3NDMgMDAwMDAgbiAKMDAwMDAwMDAxNSAwMDAwMCBuIAowMDAwMDAwMjI1IDAwMDAwIG4gCjAwMDAwMTgwMzkgMDAwMDAgbiAKMDAwMDAxOTgzMSAwMDAwMCBuIAowMDAwMDE5NzA4IDAwMDAwIG4gCjAwMDAwMTk4OTQgMDAwMDAgbiAKMDAwMDAxOTk0OCAwMDAwMCBuIAowMDAwMDE5OTgwIDAwMDAwIG4gCjAwMDAwMjAwODQgMDAwMDAgbiAKdHJhaWxlcgo8PC9JbmZvIDExIDAgUi9JRCBbPDQyMWIwOTFmZjJkZDY2MTlmOTg1YmQzNzRkNTM4Y2E2Pjw1Yjk3YmQzOGMzMjQ3ZTAxMGMwYzFiYjg3MmM0OWVjMD5dL1Jvb3QgMTAgMCBSL1NpemUgMTI+PgpzdGFydHhyZWYKMjAyNTIKJSVFT0YK";

//header('Content-Type: application/pdf');

file_put_contents('/var/www/html/fact/myFile.pdf', base64_decode($fact));
?>