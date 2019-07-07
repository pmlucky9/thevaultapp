<?php


namespace TheVaultApp\Checkout\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\Data\GroupInterface;
use TheVaultApp\Checkout\Model\Ui\ConfigProvider;
use TheVaultApp\Checkout\Gateway\Config\Config as GatewayConfig;

class PlaceOrderAjax extends Action {
    
    const EMAIL_COOKIE_NAME = 'thevaultappEmail';

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * PlaceOrder constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param GatewayConfig $gatewayConfig
     * @param CustomerSession $customerSession
     * @param QuoteManagement $quoteManagement
     * @param CookieManagerInterface $cookieManager
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        GatewayConfig $gatewayConfig,
        CustomerSession $customerSession,
        QuoteManagement $quoteManagement,
        CookieManagerInterface $cookieManager,
        JsonFactory $resultJsonFactory        
    ) {
        parent::__construct($context);
        
        $this->checkoutSession   = $checkoutSession;
        $this->customerSession   = $customerSession;
        $this->quoteManagement   = $quoteManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Handles the controller method.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute() {        
        // Load the customer quote
        $quote          = $this->checkoutSession->getQuote();

        // Check for guest email
        if ($this->customerSession->isLoggedIn() === false) {
            $quote = $this->prepareGuestQuote($quote);
        }

        // Prepare session quote info for redirection after payment
        $this->checkoutSession
        ->setLastQuoteId($quote->getId())
        ->setLastSuccessQuoteId($quote->getId())
        ->clearHelperData();

        // Set payment
        $quote->getPayment()->setMethod('substitution');
        $quote->collectTotals()->save();

        // Create the order
        $order = $this->quoteManagement->submit($quote);

        // Prepare session order info for redirection after payment
        if ($order) {
            $this->checkoutSession->setLastOrderId($order->getId())
                               ->setLastRealOrderId($order->getIncrementId())
                               ->setLastOrderStatus($order->getStatus());
        }
        
        return $this->resultJsonFactory->create()->setData([
            'trackId' => $order->getIncrementId()
        ]);
    }

    /**
     * Sets the email for guest users
     *
     * @return bool
     */
    public function prepareGuestQuote($quote, $email = null) {
        
        // Retrieve the user email 
        $guestEmail = $email
        ?? $this->customerSession->getData('checkoutSessionData')['customerEmail']
        ?? $quote->getCustomerEmail() 
        ?? $quote->getBillingAddress()->getEmail()
        ?? $this->cookieManager->getCookie(self::EMAIL_COOKIE_NAME);

        // Set the quote as guest
        $quote->setCustomerId(null)
        ->setCustomerEmail($guestEmail)
        ->setCustomerIsGuest(true)
        ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        // Delete the cookie
        $this->cookieManager->deleteCookie(self::EMAIL_COOKIE_NAME);

        return $quote;
    }    

}
