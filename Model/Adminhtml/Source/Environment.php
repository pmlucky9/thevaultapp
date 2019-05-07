<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface {

    const ENVIRONMENT_LIVE = 'live';
    const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => __('Sandbox')
            ],
            [
                'value' => self::ENVIRONMENT_LIVE,
                'label' => __('Live')
            ]
        ];
    }

}
