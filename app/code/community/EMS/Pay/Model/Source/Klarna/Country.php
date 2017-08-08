<?php

class EMS_Pay_Model_Source_Klarna_Country extends Mage_Adminhtml_Model_System_Config_Source_Country
{
    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = false)
    {
        $config = Mage::getSingleton('ems_pay/config');
        $options = parent::toOptionArray($isMultiselect);
        foreach ($options as $index => $optionData) {
            $value = $optionData['value'];
            if ($value !== '' && !$config->isCountrySupportedByKlarna($value)) {
                unset($options[$index]);
            }
        }

        return $options;
    }
}
