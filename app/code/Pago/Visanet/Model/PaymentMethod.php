<?php
 
namespace Pago\Visanet\Model;
use \Magento\Payment\Model\Method\Cc;
use \Inchoo\Helloworld\Model\ApiKalea;
/**
 * Pay In Store payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    /**
     * Payment code
     *
     * @var string
     */
    const CODE = 'visanet';

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

       /* $order = $payment->getOrder();
            $payment->setSkipTransactionCreation(true);
                 
            $state = 'pending_payment';
            $status = 'pending_payment';
            $comment = 'Charge was declined. Please, contact you bank for more information or use a different card.';
            $isNotified = false;
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusToHistory($order->getStatus(), $comment);
            $order->save(); 
            $payment->setIsTransactionPending(true); 
            $payment->setIsFraudDetected(true);*/

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

        /* $order      = $payment->getOrder();        
         $billing    = $order->getBillingAddress();
         $shipping   = $order->getShippingAddress();         


         foreach($order->getAllItems() as $item){
             $proId[]    = $item->getProductId();        
             $proName[]  = $item->getName(); 
             $proPrice[] = $item->getPrice();
             $proQty[]   = $item->getQtyToInvoice();
             $proSku[]   = $item->getSku();
         }

         // TESTING
         /*$data  = '';
         $data .= 'Order ID :'. $order->getIncrementId();
         $data .= PHP_EOL;
         $data .= 'Email :'.$order->getCustomerEmail();
         $data .= PHP_EOL;        
         $data .= 'Cc :'.$payment->getCcNumber();
         $data .= PHP_EOL;        
         $data .= 'Exp Month :'.sprintf('%02d',$payment->getCcExpMonth());
         $data .= PHP_EOL;        
         $data .= 'Exp Year :'.$payment->getMethodInstance()->getCardsStorage();
         $data .= PHP_EOL;        
         $data .= 'CCV :'.$payment->getCcCid();
         $data .= PHP_EOL;        
         $data .= 'Amount: '.$amount;
         $data .= PHP_EOL;
         $data .= 'Customer Postcode :'.$shipping->getPostcode();
         $data .= PHP_EOL;
         $data .= 'Customer Region :'.$shipping->getRegion();
         $data .= PHP_EOL;
         $data .= 'Customer Country ID :'.$shipping->getCountryId();
         $data .= PHP_EOL;        
         $data .= 'Is Customer Is Guest ?'.$order->getCustomerIsGuest();
         $data .= PHP_EOL;        
         $data .= 'Customer ID : '.$order->getCustomerId();  */            

         try {
            //$api = new ApiKalea();
            $apikalea = new ApiKalea(); 
            $order = $payment->getOrder();            
            $datosdireccion = $order->getBillingAddress()->getData();    

            $cuotas = explode("VC", $payment->getAdditionalInformation('vcc'));
            $cuotas = intval($cuotas[1]);

            $valor = self::calculartotal($cuotas, $amount);
            $order->setNcuotas($cuotas);
            $order->setVcuotas("Q. ".self::calcularcuotas($cuotas,$valor));
            // Si tiene recargo se aplica a la orden 
            if (self::recargo($cuotas)){
                $order->setSubtotal($amount);
                $order->setBaseSubtotal($amount);
                $order->setGrandTotal($valor);
                $order->setBaseGrandTotal($valor);
                $order->setTaxAmount(self::valorrecargo($amount));
                $order->setDiscountAmount(0);               
                $order->save();
            }     

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if($customerSession->isLoggedIn()) {                
                $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
                $customer = $customerFactory->load($customerSession->getCustomer()->getId());  
                $order->setCedula($customer->getData('nit')); 
            }else {
               $order->setCedula($payment->getAdditionalInformation('cedula'));
            }    


            $shipping   = $order->getShippingAddress();  
            $departamento = $apikalea->getdepartamento($shipping->getregion_id(), $shipping->getcity());             


            if ($departamento != -1){ //Busca el id del departamento de kalea

            //Actualizar cotizacion en kalea
           $actualizar_cotizacion = $apikalea->actualizar_cotizacioncontroller($customer->getData('cotizacion'), $customer->getData('idkalea'),  $order->getCedula(), $datosdireccion["telephone"], $datosdireccion["firstname"], $datosdireccion["lastname"], $valor, $shipping->getregion_id(), $departamento, $datosdireccion["street"], $order->getAllItems());   


           
           if ($actualizar_cotizacion != -1){           

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
                'nombrecliente' => $customer->getName()           
            ];
            
            /*
            Ver request */
            //self::send_email(print_r($request, true), 'julian.escobar@ipalmera.co' );
            /**/
            
           /* $order = $payment->getOrder();
            $nombrecliente  = $order->getCustomerName(); 
            $datosdireccion = $order->getBillingAddress()->getData();            
            $productos      = $order->getAllItems();

            foreach ($order->getAllItems() as $item) {
                $productos = $item->getData();
                }

            $info = $this->getInfoInstance();
            self::send_email(print_r($datosdireccion, true) );
            die();*/

            //check if payment has been authorized


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
        //$response = ['transactionId' => 0]; //todo implement API call for auth request.
        $apikalea = new ApiKalea();
        $response = self::methodpayment($request);

        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error al procesar el pago.'));
        } else {
            $res= $apikalea->crearpedidocontroller($request["no_transa_mov"]);
           /* self::send_email(print_r($res, true), 'julian.escobar@ipalmera.co' );
            throw new \Magento\Framework\Exception\LocalizedException(__($res));  
            die();    */        
            if ($res == -1){
                self::send_email("SE QUEDO", 'julian.escobar@ipalmera.co' );
              throw new \Magento\Framework\Exception\LocalizedException(__('Error al crear el pedido v.'));  
          } else {
            $apikalea->limpiarno_transa_mov();
            $apikalea->crear_factura($request["no_transa_mov"],$request["email"],$request["nombrecliente"]);
            
            
          }
        }

        return $response;
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
    $url = 'https://ws.shopshop.com.gt/CyberSource/VisaNET/GT/AuthorizationRequest/index.php?wsdl'; 
    //$url = "https://ws.shopshop.com.gt/CyberSource/VisaNET/GT/Test/AuthorizationRequest/index.php?wsdl";
    try {
    $client = new \SoapClient($url, array("trace" => 1, "exception" => 0));    
    } catch (Exception $ex) {
        //self::send_email(print_r($ex, true), 'julian.escobar@ipalmera.co' );
    //echo 'error conexion ' . $ex->getMessage(); die();
        return false;
    }
    

    $datos['terminal'] = '99526388';
    $datos['merchant'] = '020001011';
    $datos['user'] = 'E48316433D476EA8CC61BD0F9BDE5CE50C09F0FDF72A15C9B395BBCF57CE7BCD';
    $datos['pass'] = 'D11F57F931813DBF256BEA1C9463D90D6FE40A64F8ECC84A9D6D2093CB5EF6A5';
    $datos['customerID'] = $idkalea; // NUEVO
    $datos['merchant_id'] = 'visanetgt_bimagua';
    $datos['Token'] = 'illJUkUJKzVwJ7eRWQn1ljbeqwXRrARmkiP+fECvzZt4o56YaWYOe05r3GeDUMFRN8eOUdBY7osS/zxK+jqlzWlxOGJLrvs0QH5UPHSv0nqXAWF+7cSSxuZZ46VypYIls8a1rCvPi+1CAokMAgnp+mm5q+f9uGu7xYaBjhxPMbEtquFShEKbuZ8rzSy6ENjVtarHUJIptsjltA3FDimbSIyn9YKNvUeWm5o1ixpyHi91Amuewi18rN6iEDGjiDaEsLx1TsvAgITkDxA3fGdaNotLdEpTfXufBRR03fRjiNqSHLqPsJMGGpuOvAbAjAOxQbkQtixlBo6jJSjnWpWRwA==';
    $datos['CybsEpay'] = 'epay';
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
    self::send_email(print_r($client->__getLastResponse(), true), 'julian.escobar@ipalmera.co' );
    if ($doc->getElementsByTagName('responseCode')->item(0)->nodeValue == "00"){        
        self::send_emailvisanet($client->__getLastResponse(), $monto,  $email);
        $apikalea = new ApiKalea();
        $apikalea->enviarcopiatransaccion($client->__getLastResponse(), $monto,  $email, $idkalea, $nombrecliente);
        return ['transactionId' => 0];
    } else {
        self::send_email(print_r($client->__getLastResponse(), true), 'julian.escobar@ipalmera.co' );
        return false;
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

    public function send_emailvisanet($response, $monto, $email){
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
                    'monto'  => $monto,
                    'nreferencia' => $doc->getElementsByTagName('referenceNumber')->item(0)->nodeValue,
                    'nautorizacion' => $doc->getElementsByTagName('authorizationCode')->item(0)->nodeValue,             
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

     public function getmes($mes){

        if ($mes < 10){
            $mes = "0".$mes;
        }

        return $mes;
    }
}