<?php


namespace TheVaultApp\Magento2\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Logo extends Template implements RendererInterface {

    /**
     * Renders form element as HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) {
        $pattern    = '<div id="thevaultapp_adminhtml_logo"><a href="%s" target="_blank"><img src="%s" alt="TheVaultApp Logo"></a></div>';
        $url        = 'https://checkout.com';
        $src        = 'https://cdn.checkout.com/img/checkout-logo-online-payments.jpg';

        return sprintf($pattern, $url, $src);
    }
}
