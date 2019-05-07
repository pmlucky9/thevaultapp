<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

class BillingDetailsRequest extends AbstractRequest {

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject) {
        $paymentDO      = $this->subjectReader->readPayment($buildSubject);
        $order          = $paymentDO->getOrder();
        $address        = $order->getBillingAddress();

        if ($address === null) {
            return [];
        }

        return [
            'billingDetails' => [
                'addressLine1'  => $address->getStreetLine1(),
                'addressLine2'  => $address->getStreetLine2(),
                'postcode'      => $address->getPostcode(),
                'country'       => $address->getCountryId(),
                'state'         => $address->getRegionCode(),
                'city'          => $address->getCity(),
                /*
                'phone'         => [
                    'number' => $address->getTelephone(),
                ],
                */
            ],
        ];
    }

}
