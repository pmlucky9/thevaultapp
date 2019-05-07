<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class EmbeddedTheme implements ArrayInterface {

    const THEME_STANDARD = 'standard';
    const THEME_SIMPLE = 'simple';

    /**
     * Possible embedded themes
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::THEME_STANDARD,
                'label' => __('Standard')
            ],
            [
                'value' => self::THEME_SIMPLE,
                'label' => __('Simple')
            ]
        ];
    }

}
