<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class OrderCreation implements ArrayInterface {

    const BEFORE_AUTH = 'before_auth';
    const AFTER_AUTH = 'after_auth';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::BEFORE_AUTH,
                'label' => __('Before authorization')
            ],
            [
                'value' => self::AFTER_AUTH,
                'label' => __('After authorization')
            ]        
        ];
    }

}