<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * OfflinePayments Observer
 */
namespace Magento\OfflinePayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use \Inchoo\Helloworld\Model\ApiKalea;

class BeforeOrderPaymentSaveObserver implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();        
        $order = $payment->getOrder();  
        $datosdireccion = $order->getBillingAddress()->getData();  
        $shipping   = $order->getShippingAddress();  

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {                
            $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
            $customer = $customerFactory->load($customerSession->getCustomer()->getId());  
            $order->setCedula($customer->getData('nit')); 
        }else {
            $order->setCedula($payment->getAdditionalInformation('cedula'));
        }  

        if ($payment->getMethod() == "banktransfer"){
        $amount = $order->getGrandTotal();
        $valor = number_format($amount, 0, ",", ".");
        $valor = intval(str_replace(".", "", $valor));

        $apikalea = new ApiKalea(); 
    
        $departamento = $apikalea->getdepartamento($shipping->getregion_id(), $shipping->getcity());             


         if ($departamento != -1){
             $actualizar_cotizacion = $apikalea->actualizar_cotizacioncontroller($customer->getData('cotizacion'), $customer->getData('idkalea'),  $order->getCedula(), $datosdireccion["telephone"], $datosdireccion["firstname"], $datosdireccion["lastname"], $valor, $shipping->getregion_id(), $departamento, $datosdireccion["street"], $order->getAllItems()); 

               if ($actualizar_cotizacion == 1){                 
                    $apikalea->limpiarno_transa_mov();
                    $response["nreferencia"] = " - ";
                    $response["nautorizacion"] = " - ";
                    $apikalea->enviarcopiatransaccion($response, $amount,  $datosdireccion["email"], $customer->getData('idkalea'), $order, $customer->getData('nit') , $datosdireccion["street"], "Transferencia Bancaria", "-", "-"); 
               } else {                    
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error al actualizar la cotización'));  
               }
            }else{
                throw new \Magento\Framework\Exception\LocalizedException(__('Error al Encontrar el departamento.'));  
            }
       }

        $instructionMethods = [
            Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
            Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ];
        if (in_array($payment->getMethod(), $instructionMethods)) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getInstructions()
            );
        } elseif ($payment->getMethod() === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            $methodInstance = $payment->getMethodInstance();
            if (!empty($methodInstance->getPayableTo())) {
                $payment->setAdditionalInformation('payable_to', $methodInstance->getPayableTo());
            }
            if (!empty($methodInstance->getMailingAddress())) {
                $payment->setAdditionalInformation('mailing_address', $methodInstance->getMailingAddress());
            }
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
}
