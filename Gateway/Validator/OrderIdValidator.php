<?php


namespace TheVaultApp\Magento2\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use TheVaultApp\Magento2\Model\Validator\Rule;
use Magento\Sales\Model\OrderFactory;

class OrderIdValidator extends ResponseValidator {

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * OrderIdValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(ResultInterfaceFactory $resultFactory, OrderFactory $orderFactory) {
        parent::__construct($resultFactory);

        $this->orderFactory = $orderFactory;
    }

    /**
     * Returns the array of the rules.
     *
     * @return Rule[]
     */
    protected function rules() {
        return [
            new Rule('Order ID Exists', function(array $subject) {
                $response   = SubjectReader::readResponse($subject);
                $orderId    = $response['message']['trackId'];
                $order      = $this->orderFactory->create()->loadByIncrementId($orderId);

                return ! $order->isEmpty();
            }, __('TheVaultApp track ID is not matching to any orders.') ),
        ];
    }
}