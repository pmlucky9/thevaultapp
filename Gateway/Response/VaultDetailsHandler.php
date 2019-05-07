<?php

 
namespace TheVaultApp\Magento2\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use TheVaultApp\Magento2\Model\Factory\VaultTokenFactory;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Sales\Model\Order\Payment;

class VaultDetailsHandler implements HandlerInterface {

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var VaultTokenFactory
     */
    protected $vaultTokenFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * VaultDetailsHandler constructor.
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param VaultTokenFactory $vaultTokenFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory, VaultTokenFactory $vaultTokenFactory, PaymentTokenManagementInterface $paymentTokenManagement) {
        $this->paymentExtensionFactory  = $paymentExtensionFactory;
        $this->vaultTokenFactory        = $vaultTokenFactory;
        $this->paymentTokenManagement   = $paymentTokenManagement;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response) {
        /** @var $payment Payment */
        $paymentDO  = SubjectReader::readPayment($handlingSubject);
        $payment    = $paymentDO->getPayment();

        if( array_key_exists('card', $response) ) {
            $customer       = $payment->getOrder()->getCustomerId();
            $paymentToken   = $this->vaultTokenFactory->create($response['card'], $customer);

            if( ! $this->paymentTokenAlreadyExists($paymentToken)) {
                $extensionAttributes = $this->getExtensionAttributes($payment);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * @param PaymentTokenInterface $paymentToken
     * @return bool
     */
    private function paymentTokenAlreadyExists(PaymentTokenInterface $paymentToken) {
        return $this->paymentTokenManagement->getByPublicHash( $paymentToken->getPublicHash(), $paymentToken->getCustomerId() ) !== null;
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment) {
        $extensionAttributes = $payment->getExtensionAttributes();

        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

}
