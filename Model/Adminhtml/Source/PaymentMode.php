<?php


namespace TheVaultApp\Magento2\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentMode implements ArrayInterface {

    const MODE_CARDS = 'cards';
    const MODE_MIXED = 'mixed';
    const MODE_LOCAL = 'localpayments';

    /**
     * Possible payment modes
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            [
                'value' => self::MODE_CARDS,
                'label' => __('Cards')
            ],
            [
                'value' => self::MODE_MIXED,
                'label' => __('Mixed')
            ],
            [
                'value' => self::MODE_LOCAL,
                'label' => __('Local payments')
            ] 
        ];
    }         
}