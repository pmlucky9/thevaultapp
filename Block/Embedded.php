<?php

 
namespace TheVaultApp\Magento2\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;

class Embedded extends Template {

	protected $gatewayConfig;

    /**
     * Block constructor.
     */
    public function __construct(Context $context, GatewayConfig $gatewayConfig) {
        parent::__construct($context);
        $this->gatewayConfig = $gatewayConfig;
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

    public function getEmbeddedUrl() {
        return $this->gatewayConfig->getEmbeddedUrl();
    }
}
