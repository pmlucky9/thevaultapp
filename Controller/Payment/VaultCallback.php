<?php


namespace TheVaultApp\Magento2\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;
use TheVaultApp\Magento2\Model\Service\OrderService;
use TheVaultApp\Magento2\Model\Ui\ConfigProvider;
use TheVaultApp\Magento2\Model\Service\TokenChargeService;
use TheVaultApp\Magento2\Helper\Helper;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use TheVaultApp\Magento2\Model\Adapter\ChargeAmountAdapter;
use Magento\Sales\Api\OrderRepositoryInterface;


class VaultCallback extends AbstractAction {

    /**
     * @var TokenChargeService
     */
    protected $tokenChargeService;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Helper
     */
    protected $helper;

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
     * @param OrderService $orderService
     * @param Order $orderManager
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        GatewayConfig $gatewayConfig,
        OrderService $orderService,
        OrderInterface $orderInterface,
        CustomerSession $customerSession,
        TokenChargeService $tokenChargeService,
        Helper $helper,
        JsonFactory $resultJsonFactory,
        ZendClientFactory $httpClientFactory,
        InvoiceService $invoiceService,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $gatewayConfig);

        $this->checkoutSession        = $checkoutSession;
        $this->customerSession        = $customerSession;
        $this->orderService           = $orderService;
        $this->orderInterface         = $orderInterface;
        $this->tokenChargeService     = $tokenChargeService;
        $this->helper                 = $helper;
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
        $obj = json_decode(file_get_contents('php://input'), true);
        $order = $this->getAssociatedOrder($obj['subid1']);
        $payment    = $order->getPayment();
        $status = strtolower(trim($obj['status']));
        $methodId   = $payment->getMethodInstance()->getCode();
        // Update order status
        //$order->setStatus($this->gatewayConfig->getOrderStatusComplete());
        if ($status === 'approved') {
            $order->setStatus('complete');
        } else {
            $order->setStatus('canceled');

        }

        // Send the email only if it hasn't been sent
        if (!$order->getEmailSent()) {
            //$this->orderSender->send($order);
            $order->setEmailSent(1);
        }

        // Delete comments history
        foreach ($order->getAllStatusHistory() as $orderComment) {
            $orderComment->delete();
        }


        if ($status === 'approved') {
            // Create new comment
            $newComment = 'Authorized amount of ' . ChargeAmountAdapter::getStoreAmountOfCurrency($obj['amount'] * 100, 'USD') . ' ' . 'USD' . ' Transaction ID: ' . $obj['tid'];

            // Add the new comment
            $order->addStatusToHistory($order->getStatus(), $newComment, $notify = true);


            // Create the invoice
            //if ($order->canInvoice() && ($this->gatewayConfig->getAutoGenerateInvoice())) {
            // Generate the invoice
            $amount = ChargeAmountAdapter::getStoreAmountOfCurrency(
                $obj['amount'] * 100,
                'USD'
            );
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setTransactionId($obj['tid']);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
            $invoice->setBaseGrandTotal($amount);
            $invoice->register();

            // Save the invoice
            $this->invoiceRepository->save($invoice);
        }
        $this->orderRepository->save($order);
        //exit();
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
