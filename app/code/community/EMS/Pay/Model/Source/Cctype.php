<?php

class EMS_Pay_Model_Source_Cctype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $config = Mage::getSingleton('ems_pay/config');
        $options = array();
        foreach ($config->getAvailableCreditCardTypes() as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }
}
