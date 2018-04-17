<?php

class EMS_Pay_Model_Source_OperationMode
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => EMS_Pay_Model_Config::MODE_TEST,
                'label' => Mage::helper('ems_pay')->__('Test mode')
            ),
            array(
                'value' => EMS_Pay_Model_Config::MODE_PRODUCTION,
                'label' => Mage::helper('ems_pay')->__('Live mode')
            )
        );
    }
}
