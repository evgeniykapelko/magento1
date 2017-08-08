<?php

class EMS_Pay_Model_Source_Cctype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $config = Mage::getSingleton('ems_pay/config');
        $options = [];
        foreach ($config->getAvailableCreditCardTypes() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $options;
    }
}
