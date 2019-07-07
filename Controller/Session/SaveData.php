<?php

namespace TheVaultApp\Checkout\Controller\Session;
 
use TheVaultApp\Checkout\Gateway\Config\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;

class SaveData extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Context $context, CustomerSession $customerSession, Config $config)
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->config = $config;
    }
 
    public function execute()
    {
        // Get the request data
        // Get all parameters from request
        $params = $this->getRequest()->getParams();

        // Sanitize the array
        $sessionData = array_map(function($val) {
            return filter_var($val, FILTER_SANITIZE_STRING);
        }, $params);         

        // Save in session
        $this->customerSession->setData('checkoutSessionData', $sessionData);
        
    }
}
