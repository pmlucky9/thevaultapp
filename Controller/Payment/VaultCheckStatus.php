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


class VaultCheckStatus extends AbstractAction {

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
