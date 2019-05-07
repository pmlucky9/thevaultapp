<?php


namespace TheVaultApp\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver {

    const CARD_TOKEN_ID = 'card_token_id';

    /**
     * @var array
     */
    protected static $additionalInformationList = [
        self::CARD_TOKEN_ID,
    ];

    /**
     * Handles the observer for payment.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        $data           = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach (self::$additionalInformationList as $additionalInformationKey) {
            if( array_key_exists($additionalInformationKey, $additionalData) ) {
                $paymentInfo->setAdditionalInformation($additionalInformationKey, $additionalData[$additionalInformationKey]);
            }
        }
    }

}
