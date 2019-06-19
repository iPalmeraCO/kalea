<?php
namespace Ipalmera\Nitcheckout\Model\Plugin\Checkout;
class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if(!$customerSession->isLoggedIn()) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['nit'] = [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress.custom_attributes',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'options' => [],
                        'id' => 'nit-customer'
                    ],
                    'dataScope' => 'shippingAddress.custom_attributes.nit',
                    'label' => 'Cédula',
                    'provider' => 'checkoutProvider',
                    'visible' => true,
                    'validation' => [
                    'required-entry' => true,
                    ],
                    'sortOrder' => 250,
                    'id' => 'nit-customer',
                    //'value'=> "ESO"
                ];
             }
 
 
        return $jsLayout;
    }
}