<?php


namespace TheVaultApp\Checkout\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RedirectUrl extends Field {

    /**
     * Overridden method for rendering a field. In this case the field must be only for read.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        $callbackUrl= $this->getBaseUrl() . 'thevaultapp/' . $this->getControllerUrl();

        $element->setData('value', $callbackUrl);
        $element->setReadonly('readonly');

        return $element->getElementHtml();
    }

    /**
     * Returns the controller url.
     *
     * @return string
     */
    public function getControllerUrl()
    {
        return 'payment/vaultCallback';   
    }

}
