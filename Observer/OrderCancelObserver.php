<?php


namespace TheVaultApp\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use TheVaultApp\Magento2\Model\Service\OrderService;
use TheVaultApp\Magento2\Model\Ui\ConfigProvider;

class OrderCancelObserver implements ObserverInterface {

    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;    
    }

    /**
     * Handles the observer for order cancellation.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {

        // Get the order
        $order = $observer->getEvent()->getOrder();

        // Get the payment method
        $paymentMethod = $order->getPayment()->getMethod();

        // Test the current method used
        if ($paymentMethod == ConfigProvider::CODE || $paymentMethod == ConfigProvider::CC_VAULT_CODE || $paymentMethod == ConfigProvider::THREE_DS_CODE) {

            // Update the hub API for canceled order
            $this->orderService->cancelTransactionToRemote($order);    
        }

        return $this;
    }
}
