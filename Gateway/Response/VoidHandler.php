<?php

 
namespace TheVaultApp\Magento2\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

class VoidHandler extends TransactionHandler {

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction() {
        return true;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $payment) {
        return true;
    }

}
