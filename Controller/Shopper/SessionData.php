<?php


namespace TheVaultApp\Magento2\Controller\Shopper;
 
use TheVaultApp\Magento2\Gateway\Config\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;
use TheVaultApp\Magento2\Helper\Helper;

class SessionData extends Action
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;
 
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Context $context, Helper $helper, CustomerSession $customerSession, Config $config)
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->config = $config;
    }
 
    public function execute()
    {
        // Get the request data
        $sessionData = $this->getInputData();

        // Process MADA BIN
        if ($this->config->isMadaEnabled()) {
            $sessionData = $this->checkMadaBin($sessionData);
        }

        // Save in session
        $this->customerSession->setData('checkoutSessionData', $sessionData);

        // End the script
        //exit();
    }

    private function checkMadaBin($sessionData) {
        // Test the card bin and set the MADA status
        if (isset($sessionData['cardBin']) && $this->helper->isMadaBin($sessionData['cardBin'])) {
            $sessionData['isMadaBin'] = true;
        }

        return $sessionData;
    }

    private function getInputData() {

        // Get all parameters from request
        $params = $this->getRequest()->getParams();

        // Sanitize the array
        $params = array_map(function($val) {
            return filter_var($val, FILTER_SANITIZE_STRING);
        }, $params);

        return $params;
    }
}
