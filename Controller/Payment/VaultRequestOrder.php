<?php

namespace TheVaultApp\Checkout\Controller\Payment;

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

class VaultRequestOrder extends Action {

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
     * PlaceOrder constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param GatewayConfig $gatewayConfig
     * @param OrderInterface $orderInterface
     * @param CustomerSession $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        GatewayConfig $gatewayConfig,
        OrderInterface $orderInterface,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        ZendClientFactory $httpClientFactory
    ) {
        parent::__construct($context);

        $this->checkoutSession        = $checkoutSession;
        $this->customerSession        = $customerSession;
        $this->orderInterface         = $orderInterface;
        $this->gatewayConfig          = $gatewayConfig;
        $this->resultJsonFactory      = $resultJsonFactory;
        $this->httpClientFactory      = $httpClientFactory;
    }

    /**
     * Handles the controller method.
     *
     */
    public function execute() {
        // Retrieve the request parameters        
        $api_url = $this->gatewayConfig->getPublicKey();
        $token = $this->gatewayConfig->getSecretKey();
        $store = $this->gatewayConfig->getStore();        
        $quantity = 1;
        $phone = $this->getRequest()->getParam('phone');

        $orderTrackId = $this->getRequest()->getParam('orderTrackId');
        //$orderTrackId = $this->orderInterface->loadByIncrementId($this->customerSession->getData('checkoutSessionData')['orderTrackId']);
        $order = null;

        if (isset($orderTrackId)) {
            $order = $this->orderInterface->loadByIncrementId($orderTrackId);
        }        

        if ($order) {
            $subid1 = $orderTrackId;
            $amount = $order->getGrandTotal();
            $amount = number_format(floatval($amount), 2);
            $params = array (
                'token' => $token,
                'store' => $store,
                'quantity' => $quantity,
                'subid1' => $subid1,
                'phone' => $phone,
                'amount' => $amount
            );
            $client = $this->httpClientFactory->create();
            $client->setUri($api_url);
            $client->setMethod('POST');
            $client->setHeaders('Content-Type', 'application/json');
            $client->setHeaders('Accept','application/json');
            $client->setRawData(json_encode($params), 'application/json');
            $response= $client->request('POST');
            
            try {
                $result = json_decode($response->getBody(), true);
                if ($result['status'] == 'ok') {
                    return $this->resultJsonFactory->create()->setData([
                        'status' => 'ok',
                        'data' => [
                            'token' => $token,
                            'store' => $store,
                            'quantity' => $quantity,
                            'subid1' => $subid1,
                            'requestid' => $result['data']['requestid'],
                            'count' => $result['data']['count'],
                            'phone' => $result['data']['phone'],
                            'amount' => $amount,
                        ]
                    ]);
                } else {
                    $result = array_merge([
                        'data' => [
                            'token' => $token,
                            'store' => $store,
                            'quantity' => $quantity,
                            'subid1' => $subid1,
                            'amount' => $amount,
                        ]
                    ], $result);
                    return $this->resultJsonFactory->create()->setData($result);
                }
            } catch(\Exception $err) {
                return $this->resultJsonFactory->create()->setData([
                    "status" => 'error',
                    "errors" => 'json parse error',
                    'data' => [
                        'token' => $token,
                        'store' => $store,
                        'quantity' => $quantity,
                        'subid1' => $subid1,
                        'amount' => $amount,
                    ]
                ]);
            }
        }
        return $this->resultJsonFactory->create()->setData([
            "status" => 'error',
            "errors" => 'wrong order'
        ]);
    }
}
