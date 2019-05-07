<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

use TheVaultApp\Magento2\Model\Adapter\ChargeAmountAdapter;

class AmountRequest extends AbstractRequest {

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
        $amount         = ChargeAmountAdapter::getPaymentFinalCurrencyValue($this->subjectReader->readAmount($buildSubject));

        $currencyCode   = ChargeAmountAdapter::getPaymentFinalCurrencyCode($order->getCurrencyCode());
        $value          = ChargeAmountAdapter::getGatewayAmountOfCurrency($amount, $currencyCode);

        return compact('value');
    }

}
