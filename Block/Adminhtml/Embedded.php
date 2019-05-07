<?php

 
namespace TheVaultApp\Magento2\Block\Adminhtml;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config as PaymentModelConfig;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;

class Embedded extends \Magento\Payment\Block\Form\Cc {

    /**
     * @var String
     */
    protected $_template;

    /**
     * @var Config
     */
	protected $gatewayConfig;

    /**
     * Block constructor.
     */
    public function __construct(
        Context $context,
        GatewayConfig $gatewayConfig,
        PaymentModelConfig $paymentModelConfig
    ) {
        parent::__construct($context, $paymentModelConfig);
        $this->_template = 'TheVaultApp_Magento2::embedded.phtml';
        $this->gatewayConfig = $gatewayConfig;
    }
           
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('payment_form_block_to_html_before', ['block' => $this]);
        return parent::_toHtml();
    }

    public function getEmbeddedCss() {
        return $this->gatewayConfig->getEmbeddedCss();
    }

    public function hasCustomCss() {

    	// Get the custom CSS file
        $css_file = $this->_scopeConfig->getValue('payment/thevaultapp/thevaultapp_base_settings/css_file');
		$custom_css_file = $this->_scopeConfig->getValue('payment/thevaultapp/thevaultapp_base_settings/custom_css');

		// Determine if there is a custom CSS file
		return (bool) (isset($custom_css_file) && !empty($custom_css_file) && $css_file == 'custom');
    }

    public function getPublicKey() {
        return $this->gatewayConfig->getPublicKey();
    }

    public function getEmbeddedUrl() {
        return $this->gatewayConfig->getEmbeddedUrl();
    }
    
    public function getEmbeddedTheme() {
        return $this->gatewayConfig->getEmbeddedTheme();
    }

    public function getDebugMode() {
        return $this->gatewayConfig->isDebugMode();
    }

    public function getIntegrationLanguage() {
        return $this->gatewayConfig->getIntegrationLanguage();
    }
}
