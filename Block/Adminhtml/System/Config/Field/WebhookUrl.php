<?php


namespace TheVaultApp\Magento2\Block\Adminhtml\System\Config\Field;

class WebhookUrl extends AbstractCallbackUrl {

    /**
     * Returns the controller url.
     *
     * @return string
     */
    public function getControllerUrl() {
        //return 'webhook/callback';
        return 'payment/vaultCallback';
    }
}
