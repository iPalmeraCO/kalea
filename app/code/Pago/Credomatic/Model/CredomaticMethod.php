<?php
 
namespace Pago\Credomatic\Model;
use \Magento\Payment\Model\Method\Cc;
use \Inchoo\Helloworld\Model\ApiKalea;
/**
 * Pay In Store payment method model
 */
class CredomaticMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    /**
     * Payment code
     *
     * @var string
     */
    const CODE = 'credomatic';

    protected $_code = self::CODE;

    protected $_canAuthorize = true;
    protected $_canCapture = true;



    /**
     * Capture Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
       /* $apikalea = new ApiKalea();    

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {                
                $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
                $customer = $customerFactory->load($customerSession->getCustomer()->getId());  
        }

         $ncoti = $customer->getData('cotizacion');
            if ($ncoti == ""){


                //throw new \Magento\Framework\Exception\LocalizedException(__('Error al cargar la cotización')); 
                //$apikalea->limpiarcarrito();
                die();

                
                //No tiene cargada cotización
            }else {
                $coti = $apikalea->ver_cotizacion($ncoti);
                if (!$coti["respuesta"]){
                    //No se encontro la cotización asociada
                    $ped = $apikalea->consultar_pedido($ncoti);
                    if (!$ped["respuesta"]){  
                        die();
                        throw new \Magento\Framework\Exception\LocalizedException(__('Error al cargar la cotización')); 
                    }
                    die();
                }
            }*/

         //throw new \Magento\Framework\Exception\LocalizedException(__('Error al buscar departamento'));

        try {
            //check if payment has been authorized
            if(is_null($payment->getParentTransactionId())) {
                $this->authorize($payment, $amount);
            }

            //build array of payment data for API request.
            $request = [
                'capture_amount' => $amount,
                //any other fields, api key, etc.
            ];           

            $payment->setStatus(self::STATUS_DECLINED);
            $payment->setCcTransId(NULL); 

            //make API request to credit card processor.
            $response = $this->makeCaptureRequest($request);

            //todo handle response

            //transaction is done.
            $payment->setIsTransactionClosed(1);

        } catch (\Exception $e) {
            $this->debug($payment->getData(), $e->getMessage());
        }

        return $this;
    }

    /**
     * Authorize a payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

       
         try {
            
            $apikalea = new ApiKalea();                         
            $order = $payment->getOrder();            
            $datosdireccion = $order->getBillingAddress()->getData();                

            $valor =  $amount;
            $order->setNcuotas(0);
            $order->setVcuotas($amount);
            

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if($customerSession->isLoggedIn()) {                
                $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
                $customer = $customerFactory->load($customerSession->getCustomer()->getId());  
                $order->setCedula($customer->getData('nit')); 
            }else {
               $order->setCedula($payment->getAdditionalInformation('cedula'));
            }    

            /*$ncoti = $customer->getData('cotizacion');
            if ($ncoti == ""){
                 
                //$apikalea->limpiarcarrito();
                die();

                
                //No tiene cargada cotización
            }else {
                $coti = $apikalea->ver_cotizacion($coti);
                if (!$coti["respuesta"]){
                    //No se encontro la cotización asociada
                    $ped = $apikalea->consultar_pedido($ncoti);
                    if (!$ped["respuesta"]){  
                        //No tiene pedido asociado
                    }
                }
            }*/

            
            $shipping   = $order->getShippingAddress();  
            $departamento = $apikalea->getdepartamento($shipping->getregion_id(), $shipping->getcity());             


            if ($departamento != -1){ //Busca el id del departamento de kalea

            //Actualizar cotizacion en kalea

            //Buscar si esta en cotización
            $apikalea = new ApiKalea();           
            $actualizar_cotizacion = $apikalea->actualizar_cotizacioncontroller($customer->getData('cotizacion'), $customer->getData('idkalea'),  $order->getCedula(), $datosdireccion["telephone"], $datosdireccion["firstname"], $datosdireccion["lastname"], $valor, $shipping->getregion_id(), $departamento, $datosdireccion["street"], $order->getAllItems());   



           
           if ($actualizar_cotizacion == 1){           

            ///build array of payment data for API request.
            $request = [
                'cc_number' => $payment->getAdditionalInformation('cc_number'),  
                'cc_exp_month' => $payment->getAdditionalInformation('cc_exp_month'),  
                'cc_exp_year' => $payment->getAdditionalInformation('cc_exp_year'),  
                'cc_cid' => $payment->getAdditionalInformation('cc_cid'),  
                'vname' => $payment->getAdditionalInformation('vname'),  
                'lastname' => $payment->getAdditionalInformation('lastname'),  
                'vcc' => $payment->getAdditionalInformation('vcc'),  
                'amount' => $valor,
                'codpostal' => $datosdireccion["postcode"],
                'direccion' => $datosdireccion["street"],
                'ciudad'    => $datosdireccion["city"],
                'region'    => $datosdireccion["region"], 
                'email'     => $datosdireccion["email"],
                'no_transa_mov' => $customer->getData('cotizacion'),    
                'idkalea'   => $customer->getData('idkalea'),
                'nombrecliente' => $customer->getName(),
                'nit' => $customer->getData('nit'),
                'order' => $order                  
            ];        

            $response = $this->makeAuthRequest($request);
            
            
            }else {            
             throw new \Magento\Framework\Exception\LocalizedException(__('Error al generar la cotización'));
            } 

            }else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error al buscar departamento'));
        }


        } catch (\Exception $e) {
            $this->debug($payment->getData(), $e->getMessage());
        }

        if(isset($response['transactionID'])) {

            // Successful auth request.
            // Set the transaction id on the payment so the capture request knows auth has happened.
            $payment->setTransactionId($customer->getData('cotizacion'));
            $payment->setParentTransactionId($customer->getData('cotizacion'));
        }

        //processing is not done yet.
        $payment->setIsTransactionClosed(0);
        
        return $this;
    }

    /**
     * Set the payment action to authorize_and_capture
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return self::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Test method to handle an API call for authorization request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeAuthRequest($request)
    {        
        $apikalea = new ApiKalea();   
        $response = self::methodpayment($request);
        //self::send_email(print_r($response,true),"julian.escobar@ipalmera.co");

        if(is_array($response)) {
            $apikalea->limpiarno_transa_mov();
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error al procesar el pago.'));
        }

        return $response;;
    }

    /**
     * Test method to handle an API call for capture request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeCaptureRequest($request)
    {
        $response = ['success']; //todo implement API call for capture request.
        
        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed capture request.'));
        }

        return $response;
    }

     /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $transactionId = $payment->getParentTransactionId();
 
        try {
            \Stripe\Charge::retrieve($transactionId)->refund(['amount' => $amount * 100]);
        } catch (\Exception $e) {
            $this->debugData(['transaction_id' => $transactionId, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment refunding error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment refunding error.'));
        }
 
        $payment
            ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);
 
        return $this;
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }

    public function methodpayment($request){   
        $apikalea = new ApiKalea();
        
        //Consultar si ya tiene pago registrado
        if ($apikalea->getpago($request["no_transa_mov"]) == -1)
            { 
                $month     = $request["cc_exp_month"];
                $cc_number = $request["cc_number"];
                $year      = $request["cc_exp_year"];
                $cvv       = $request["cc_cid"];
                //$vc  //numero cuotas
                $firstName = $request["vname"];
                $lastname  = $request["lastname"];
                $monto     = $request["amount"];    
                $ciudad    = $request["ciudad"];
                $region    = $request["region"];
                $direccion = $request["direccion"];
                $postal    = $request["codpostal"];
                $email     = $request["email"];    
                $vcc       = $request["vcc"]; 
                $idkalea   = $request["idkalea"];   
                $nombrecliente = $request["nombrecliente"];   
                //$url = 'https://ws.shopshop.com.gt/CyberSource/VisaNET/GT/AuthorizationRequest/index.php?wsdl'; 
                $url = "https://ws.shopshop.com.gt/CyberSource/VisaNET/GT/Test/AuthorizationRequest/index.php?wsdl";
                try {
                $client = new \SoapClient($url, array("trace" => 1, "exception" => 0));
                } catch (Exception $ex) {
                //echo 'error conexion ' . $ex->getMessage(); die();
                    return false;
                }

                $datos['terminal'] = '99526388';
                $datos['merchant'] = '020001011';
                $datos['user'] = 'E48316433D476EA8CC61BD0F9BDE5CE50C09F0FDF72A15C9B395BBCF57CE7BCD';
                $datos['pass'] = 'D11F57F931813DBF256BEA1C9463D90D6FE40A64F8ECC84A9D6D2093CB5EF6A5';
                $datos['customerID'] = $idkalea; // NUEVO
                /*$datos['merchant_id'] = 'visanetgt_bimagua';
                $datos['Token'] = 'illJUkUJKzVwJ7eRWQn1ljbeqwXRrARmkiP+fECvzZt4o56YaWYOe05r3GeDUMFRN8eOUdBY7osS/zxK+jqlzWlxOGJLrvs0QH5UPHSv0nqXAWF+7cSSxuZZ46VypYIls8a1rCvPi+1CAokMAgnp+mm5q+f9uGu7xYaBjhxPMbEtquFShEKbuZ8rzSy6ENjVtarHUJIptsjltA3FDimbSIyn9YKNvUeWm5o1ixpyHi91Amuewi18rN6iEDGjiDaEsLx1TsvAgITkDxA3fGdaNotLdEpTfXufBRR03fRjiNqSHLqPsJMGGpuOvAbAjAOxQbkQtixlBo6jJSjnWpWRwA==';*/
                $datos['merchant_id'] = 'visanetgt_kalea';
                $datos['Token'] = "U+dvnLRwAzURZh6LXETkf0qiT8ibGzEMyKexbgHA62+fzJkFEtC9CREEK0FMivmeyyVRElwiXHp4Ubej5bwiIFpuBfzZqVC3aBwLuTgO8QhMJHa+4q27yg8WyJfFwu5VbidXO7qGMkMpd5Gl1TRdaxFcJ2ahBXzjfMDLMlvbUSWA1PZJZ9MduNKmYDSpf07K56K5KYjMY9Z/ydfbnvvc24WdJOrCPygekmMCZPk7z8DlF2S0uD/2q8c2SUc67eOyyzOv0uY0wVXXPzQPvmLTjWnGmasy7Bx/hQfKs8P7GY8x648I+8q5972vpBDabsmuNHpkItyIr8yh3WR+V7jmVA==";
                $datos['CybsEpay'] = 'cybersoure';
                $datos['expirationMonth'] = self::getmes($month);
                $datos['expirationYear'] = substr($year,-2);
                $datos['accountNumber'] = $cc_number;
                $datos['cvv'] = $cvv;
                $datos['monto'] = $monto;
                $datos['vc'] = $vcc;
                $datos['firstName'] = self::quitaracentos($firstName);
                $datos['lastName'] = self::quitaracentos($lastname);
                $datos['merchantReferenceCode'] = '0003';
                $datos['currency'] = 'GTQ';
                $datos['field10'] = 'WEB';
                $datos['country'] = 'GT';
                $datos['city'] = self::quitaracentos($ciudad);
                $datos['state'] = self::quitaracentos($region);
                $datos['street'] = self::quitaracentos($direccion);
                $datos['postalCode'] = $postal;
                $datos['email'] = self::quitaracentos($email);
                self::send_email(print_r($datos, true), 'julian.escobar@ipalmera.co' );
                
                try {
                $response = $client->Authorization($datos);
                } catch (Exception $ex) {
                //echo 'error consumno ' . $ex->getMessage(); die();
                //self::send_email(print_r($ex->getMessage(), true), 'julian.escobar@ipalmera.co' );
                return false;
                }
                $doc = new \DOMDocument;
                $doc->loadXML($client->__getLastResponse());
                if ($doc->getElementsByTagName('responseCode')->item(0)->nodeValue == "100"){        
                    $response = self::getresponsevisanet($client->__getLastResponse());
                    self::send_emailvisanet($response, $monto,  $email);                
                    $apikalea->registrarpago($request["no_transa_mov"],1,$monto,1,$monto,$response['nreferencia'],$response['nautorizacion'],$nombrecliente, $email);                   
                    $apikalea->enviarcopiatransaccion($response, $monto,  $email, $idkalea, $request["order"], $request["nit"], $direccion, "Contado", "-", "-");                    
                    return ['transactionId' => $request["no_transa_mov"]];
                } else {
                    self::send_email(print_r($client->__getLastResponse(), true), 'julian.escobar@ipalmera.co' );
                    return false;
                } 
            } else {
                return ['transactionId' => $request["no_transa_mov"]];
            }   
    }

     public function send_email($datos, $email){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
        $templateVars = [
                    'subject'    => 1, 
                    'fecha'  => $datos            
                ];     
        $data = $transport
            ->setTemplateIdentifier(12)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
            ->setTemplateVars($templateVars)
            ->setFrom(['name' => 'Notificación Pago Éxitoso','email' => 'magento_back@pa-phone.com'])           
            ->addTo([$email])
            //->addTo(['sistemas@kalea.com.gt'])
            ->getTransport();

        $data->sendMessage();
    }

    public function getmes($mes){

        if ($mes < 10){
            $mes = "0".$mes;
        }

        return $mes;
    }
    public function send_emailvisanet($response, $monto, $email){
        $monto = number_format($monto, 0, ".", ",");      
        date_default_timezone_set('America/Guatemala');
        $magentoDate = date('m/d/Y h:i:s a');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $transport = $objectManager->create('Magento\Framework\Mail\Template\TransportBuilder'); 
        $templateVars = [
                    'subject'    => 1, 
                    'fecha'  => $magentoDate,
                    'monto'  => $monto,
                    'nreferencia' => $response["nreferencia"],
                    'nautorizacion' => $response["nautorizacion"],             
                ];     
        $data = $transport
            ->setTemplateIdentifier(12)//get temptate id in your create in backend to use variable in backend you should use this tpye format etc . {{var message}} for message  {{var order_no}} for order id
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1])
            ->setTemplateVars($templateVars)
            ->setFrom(['name' => 'Notificación Pago Éxitoso','email' => 'magento_back@pa-phone.com'])           
            ->addTo([$email])
            //->addTo(['sistemas@kalea.com.gt'])
            ->getTransport();

        $data->sendMessage();
    }

    /*Retornar el nreferencia y el nautorizacion de visanet*/
    public function getresponsevisanet($response){
         $doc = new \DOMDocument;
         $doc->loadXML($response);
         return array('nreferencia' => $doc->getElementsByTagName('referenceNumber')->item(0)->nodeValue,
                'nautorizacion' => $doc->getElementsByTagName('authorizationCode')->item(0)->nodeValue);
    }

    public function quitaracentos($cadena){        
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
        $cadena = strtolower($cadena);
        return utf8_encode($cadena);
    }

      public function calculartotal($ncuotas, $total){      
        $total = $total;
        if (self::recargo($ncuotas)){           
            $total = self::valorrecargo($total) + $total;
            $total = number_format($total, 0, ",", ".");
            $total = intval(str_replace(".", "", $total));
        }

         return $total;     

    }

    /* Retorna si tiene recargo de 10% si son mas de 12 cuotas*/ 
    public function recargo($ncuotas){
        if ($ncuotas > 10){
            return true;
        } else {
            return false;
        }

    }

    /*Retorna el 10% del valor */
    public function valorrecargo($total){
        return $total * 0.07;
    }

    /* Calular Valor de cuotas */
    public function calcularcuotas($ncuotas, $total) {
        $ncuotas = $total / intval($ncuotas);
        return number_format($ncuotas, 0, ".", ",");
    }

    /*public function creardetallescotizacion($mov, $productos){
        $apikalea = new ApiKalea();
        $success = true;

        foreach ($productos as $prod) {             
             $desc  = 0;
             $pre   = $prod->getPrice();
             $can   = $prod->getQtyToInvoice();
             $sku   = $prod->getSku();
           
             $resp =$apikalea->crear_detalle($mov,$sku,$can,$pre,$desc,$pre,$can);

             if (!$resp["respuesta"]){
                $success = false;
                break;
             }

        }

        return $success;

    }

     public function crearfactura ($no_transa_mov){
        $apikalea = new ApiKalea();        
        return $apikalea->crear_factura($no_transa_mov);
    }*/
}