<?php

 
namespace TheVaultApp\Magento2\Model\Fields;

use Magento\Framework\Data\OptionSourceInterface;
 
class PlanStatus implements OptionSourceInterface
{
    /**
     * Get Grid row status type labels array.
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            '0' => __('Failed Initial'),
            '1' => __('Active'),
            '2' => __('Cancelled'),
            '3' => __('In Arrears'),
            '4' => __('Suspended'),
            '5' => __('Completed'),
        ];

        return $options;
    }
 
    /**
     * Get Grid row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
 
    /**
     * Get Grid row type array for option element.
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}