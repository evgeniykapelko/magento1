<?php

class EMS_Pay_Model_Source_Currency extends Mage_Adminhtml_Model_System_Config_Source_Currency
{
    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect)
    {
        $currency = Mage::getSingleton('ems_pay/currency');
        $options = parent::toOptionArray($isMultiselect);
        foreach ($options as $index => $optionData) {
            $value = $optionData['value'];
            if ($value !== '' && !$currency->isCurrencySupported($value)) {
                unset($options[$index]);
            } elseif ($value !== '') {
                $options[$index]['label'] = $currency->getCurrencyLabel($value);
            }
        }

        return $options;
    }
}
