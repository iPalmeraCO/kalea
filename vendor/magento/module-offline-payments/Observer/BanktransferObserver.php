<?php
namespace Magento\OfflinePayments\Observer;


    use Magento\Framework\DataObject;
    use Magento\Framework\Encryption\EncryptorInterface;
    use Magento\Framework\Event\Observer;
    use Magento\Framework\Exception\LocalizedException;
    use Magento\Payment\Observer\AbstractDataAssignObserver;
    use Magento\Quote\Api\Data\PaymentInterface;
    use Magento\Payment\Model\InfoInterface;

 class BanktransferObserver extends AbstractDataAssignObserver
{
    
    /**
     * @var array
     */
    protected $additionalInformationList = [
        'cedula'       
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}