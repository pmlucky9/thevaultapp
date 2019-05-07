<?php


namespace TheVaultApp\Magento2\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;
use TheVaultApp\Magento2\Model\Service\OrderService;
use TheVaultApp\Magento2\Model\Ui\ConfigProvider;
use TheVaultApp\Magento2\Model\Service\TokenChargeService;
use TheVaultApp\Magento2\Helper\Helper;

class PlaceOrder extends AbstractAction {

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
        Helper $helper
    ) {
        parent::__construct($context, $gatewayConfig);

        $this->checkoutSession        = $checkoutSession;
        $this->customerSession        = $customerSession;
        $this->orderService           = $orderService;
        $this->orderInterface         = $orderInterface;
        $this->tokenChargeService     = $tokenChargeService;
        $this->helper                 = $helper;
    }

    /**
     * Handles the controller method.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute() {

        // Retrieve the request parameters
        $params = array(
            'cardToken' => $this->getRequest()->getParam('cko-card-token'),
            'email' => $this->getRequest()->getParam('cko-context-id'),
            'agreement' => array_keys($this->getRequest()->getPostValue('agreement', [])),
            'quote' => $this->checkoutSession->getQuote()
        );

        $order = null;
        if (isset($this->customerSession->getData('checkoutSessionData')['orderTrackId'])) {
            $order = $this->orderInterface->loadByIncrementId($this->customerSession->getData('checkoutSessionData')['orderTrackId']);
        }

        if ($order) {
            $this->updateOrder($params, $order);
        }
        else {
            $this->createOrder($params);
        }
    }

    public function updateOrder($params, $order) {

        // Create the charge for order already placed
        $updateSuccess = $this->tokenChargeService->sendChargeRequest($params['cardToken'], $order);

        // Update payment data
        $order = $this->updatePaymentData($order);
        
        // 3D Secure redirection if needed
        if(!$this->isUrl($this->checkoutSession->get3DSRedirect())) {
            return $this->_redirect('checkout/onepage/success', ['_secure' => true]);
        }
        return $this->place3DSecureRedirectUrl();
        //exit();
    }

    public function createOrder($params) {
        // Check for guest email
        if ($this->customerSession->isLoggedIn() === false) {
            $params['quote'] = $this->helper->prepareGuestQuote($params['quote'], $params['email']);
        }

        // Perform quote and order validation
        try {
            // Create an order from the quote
            $this->validateQuote($params['quote']);
            
            /**
             *  Temporary workaround for a M2 code T&C checkbox issue not sending data.
             *  The last parameter should be $params['agreement']
             */
            $this->orderService->execute($params['quote'], $params['cardToken'], array(true));

            // 3D Secure redirection if needed
            if ($this->isUrl($this->checkoutSession->get3DSRedirect())) {
                return $this->place3DSecureRedirectUrl();
            }
            return $this->_redirect('checkout/onepage/success', ['_secure' => true]);
            //exit();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }

    public function updatePaymentData($order) {
        // Load payment object
        $payment = $order->getPayment();

        // Set the payment method, previously "substitution" for pre auth order creation
        $payment->setMethod(ConfigProvider::CODE); 
        $payment->save();
        $order->save();

        return $order;
    }

    /**
     * Listens to a session variable set in Gateway/Response/ThreeDSecureDetailsHandler.php.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function place3DSecureRedirectUrl() {
        $url = $this->checkoutSession->get3DSRedirect();
        $this->checkoutSession->uns3DSRedirect();

        return $this->getResponse()->setBody('<script type="text/javascript">'.
            'function waitForElement() {'.
            'var redirectUrl = "' . $url . '";'.
            'if (redirectUrl.length !== 0){ window.location.replace(redirectUrl); }'.
            'else { setTimeout(waitForElement, 250); }'.
            '} '.
            'waitForElement();'.
            '</script>');
    }

    public function isUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) === false ? false : true;
    }
}
