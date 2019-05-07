<?php


namespace TheVaultApp\Magento2\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Backend\Model\Auth\Session as BackendAuthSession;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var BackendAuthSession
     */
    protected $backendAuthSession;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        BackendAuthSession $backendAuthSession
    ) {
        $this->backendAuthSession    = $backendAuthSession;
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer)
    {
        // Get the order
        $order = $observer->getEvent()->getOrder();

        // Get the method id
        $methodId = $order->getPayment()->getMethodInstance()->getCode();

        if ($this->backendAuthSession->isLoggedIn() && $methodId == 'thevaultapp_admin_method') {



        }
        
        return $this;
    }
}
