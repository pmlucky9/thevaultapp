<?php


namespace TheVaultApp\Checkout\Controller\Payment;

use DomainException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
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


class VaultCallback extends Action implements CsrfAwareActionInterface{

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
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request 
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Handles the controller method.
     *
     */
    public function execute() {
        $request_text = file_get_contents('php://input');
        file_put_contents('callback.txt', $request_text);
        $obj = [];
        $order = null;
        $payment = null;
        $status = '';
        $methodId = null;
        try {
            $obj = json_decode($request_text, true);
            $order = isset($obj['subid1']) ? $this->getAssociatedOrder($obj['subid1']) : null;
            if (!is_null($order)) {
                $payment = $order->getPayment();
                $status = strtolower(trim($obj['status']));
                $methodId = $payment->getMethodInstance()->getCode();
            }
        } catch (\Exception $ex) {

        }

        if (is_null($order)) {
            return $this->resultJsonFactory->create()->setData([
                "status" => "failed",
                "request_text" => $request_text
            ]);
        }


        // Update order status
        //$order->setStatus($this->gatewayConfig->getOrderStatusComplete());

        if ($status == 'approved') {
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


        if ($status == 'approved') {
            // Create new comment
            $newComment = 'Authorized amount of ' . $this->getStoreAmountOfCurrency($obj['amount'] * 100, 'USD') . ' ' . 'USD' . ' Transaction ID: ' . $obj['tid'];

            // Add the new comment
            $order->addStatusToHistory($order->getStatus(), $newComment, $notify = true);


            // Create the invoice
            //if ($order->canInvoice() && ($this->gatewayConfig->getAutoGenerateInvoice())) {
            // Generate the invoice
            $amount = $this->getStoreAmountOfCurrency(
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
        return $this->resultJsonFactory->create()->setData([
            "status" => $status,
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
        if(!isset($order) || is_null($order) || $order->isEmpty()) {
            throw new DomainException('The order does not exists.');
        }
        return $order;
    }

    /**
     * Returns transformed amount by the given currency code which can be handled by the store.
     *
     * @param string|int $amount Value from the gateway.
     * @param $currencyCode
     * @return float
     */
    public static function getStoreAmountOfCurrency($amount, $currencyCode) {
        $currencyCode   = strtoupper($currencyCode);
        $amount         = (int) $amount;
        return (float) ($amount / 100);
    }
}
