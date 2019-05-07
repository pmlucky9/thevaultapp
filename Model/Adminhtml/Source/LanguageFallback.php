<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use TheVaultApp\Magento2\Gateway\Config\Config as GatewayConfig;

class LanguageFallback implements ArrayInterface {

    /**
     * Language fallback
     *
     * @return array
     */
    public function toOptionArray() {
        return GatewayConfig::getSupportedLanguages();
    }
}