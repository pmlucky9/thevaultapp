<?php


namespace TheVaultApp\Checkout\Controller\Payment;

use DomainException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use TheVaultApp\Checkout\Gateway\Config\Config as GatewayConfig;
use TheVaultApp\Checkout\Model\Ui\ConfigProvider;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class VaultCheckStatus extends Action {

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var GatewayConfig
     */
    protected $gatewayConfig;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;



    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param GatewayConfig $gatewayConfig
     * @param OrderInterface $orderInterface
     * @param Order $orderManager
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        GatewayConfig $gatewayConfig,
        OrderInterface $orderInterface,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        ZendClientFactory $httpClientFactory,
        InvoiceService $invoiceService,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);

        $this->checkoutSession        = $checkoutSession;
        $this->customerSession        = $customerSession;
        $this->orderInterface         = $orderInterface;
        $this->gatewayConfig          = $gatewayConfig;
        $this->resultJsonFactory      = $resultJsonFactory;
        $this->httpClientFactory      = $httpClientFactory;
        $this->invoiceService         = $invoiceService;
        $this->invoiceRepository      = $invoiceRepository;
        $this->orderRepository        = $orderRepository;
    }

    /**
     * Handles the controller method.
     *
     */
    public function execute() {
        $subid1 = $this->getRequest()->getParam('subid1');
        $order = $this->getAssociatedOrder($subid1);
        $status = $order->getStatus();
        return $this->resultJsonFactory->create()->setData([
            "status" => 'ok',
            'data' => [
                'status' => $status
            ]
        ]);
    }

    /**
     * Returns the order instance.
     *
     * @return \Magento\Sales\Model\Order
     * @throws DomainException
     */
    private function getAssociatedOrder($orderId) {
        $order = $this->orderInterface->loadByIncrementId($orderId);
        if($order->isEmpty()) {
            throw new DomainException('The order does not exists.');
        }
        return $order;
    }
}
