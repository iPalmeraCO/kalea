<?php
/*
*Clase Credomatic:
*
*	Propiedades:
*		privado $data:	datos que se envian al servidor de Credomatic;
*		Privado $url:	Url del servidor que procesa la transaccion;
*	
*	Constructor:
*		Parametros:
*			$orderid:	Identificador de la orden.
*			$type:		Tipo de transaccion ["sale", "return", "authorization"].
*			$key_id:	Identificador de la llave en el servidor.
*			$key:		Llave usada para autentificarse.
*			$cc_number:	Numero de la tarjeta de credito.
*			$cc_cvv:	Codigo de Verificacion den la tarjeta de credito.
*			$cc_exp:	Fecha de Expiracion de la tarjeta de credito en dormato mmAA.
*			$cc_fname:	Nombre del titular de la tarjeta.
*			$cc_lname:	Apellido del titular de la tarjeta.
*			$amount:	Monto a procesar.
*	
*
*	Metodos:
*		procesarpago:
*			Parametros:
*				Ninguno.
*			
*			Retorna:
*				Arreglo con la siguiette estructura:
*					response	
*					responsetext	
*					authcode	
*					transactionid	
*					avsresponse	
*					cvvresponse	
*					orderid	
*					type	
*					response_code	
*					username
*					time	
*					amount	
*					hash
*
*/
	

class credomatic{
	private $data;
	private $url = "https://credomatic.compassmerchantsolutions.com/api/transact.php";

	public function __construct($orderid, $type, $key_id, $key, $cc_number, $cc_cvv, $cc_exp, $cc_fname,$cc_lname, $amount) {
		
		$time = time();
		$hash = md5("$orderid|$amount|$time|$key");

		$this->data = array(
			"orderid"=>$orderid,
			"type"=> $type,
			"key_id"=> $key_id,
			"hash"=> $hash,
			"time"=> $time,
			"redirect"=> "http://localhost",
			"ccnumber"=> $cc_number,
			"cvv"=> $cc_cvv,
			"ccexp"=> $cc_exp,
			"firstname"=> $cc_fname,
			"lastname"=> $cc_lname,
			"amount"=> $amount
		);
	}

	public function procesarpago(){
		$httpquery = http_build_query($this->data);

		$navegacion = curl_init($this->url);
		curl_setopt ($navegacion, CURLOPT_POST, true);
		curl_setopt ($navegacion, CURLOPT_POSTFIELDS, $httpquery);
		curl_setopt($navegacion, CURLOPT_USERAGENT, "Negblog_Sistema_Integrado");
		curl_setopt ($navegacion, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($navegacion, CURLOPT_HEADER, true);
		$returndata = curl_exec ($navegacion);
		$returnlines = explode("\n", $returndata);
		$respuesta="";
		foreach ($returnlines as $element){
			$splitArrays = explode (": ", $element);
			if ($splitArrays[0] == "Location"){
				$respuesta = $splitArrays[1];
				break;
			}
		}
		$respuesta = str_replace("http://localhost?", "", $respuesta);
		parse_str($respuesta, $response);
		return $response;
				
	}
}

?>