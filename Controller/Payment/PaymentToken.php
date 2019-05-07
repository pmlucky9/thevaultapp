<?php


namespace TheVaultApp\Magento2\Controller\Payment;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use TheVaultApp\Magento2\Model\Service\PaymentTokenService;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;

class PaymentToken extends AbstractAction {

    /**
     * @var PaymentTokenService
     */
    protected $paymentTokenService;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * PaymentToken constructor.
     * @param PaymentTokenService $paymentTokenService
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context, 
        PaymentTokenService $paymentTokenService,
        JsonFactory $resultJsonFactory,
        GatewayConfig $gatewayConfig
    ) 
    {
        parent::__construct($context, $gatewayConfig);
        $this->paymentTokenService  = $paymentTokenService;
        $this->resultJsonFactory    = $resultJsonFactory;
    }

    /**
     * Handles the controller method.
     */
    public function execute() {
        $request = $this->getRequest();
        if ($request->isAjax()) {
            return $this->resultJsonFactory->create()->setData([
                'payment_token' => $this->paymentTokenService->getToken()
            ]);
        }
    }
}
