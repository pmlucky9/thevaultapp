<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class CssFile implements ArrayInterface {

    const CSS_FILE_DEFAULT = 'default';
    const CSS_FILE_CUSTOM = 'custom';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::CSS_FILE_DEFAULT,
                'label' => __('Default')
            ],
            [
                'value' => self::CSS_FILE_CUSTOM,
                'label' => __('Custom')
            ]        
        ];
    }

}