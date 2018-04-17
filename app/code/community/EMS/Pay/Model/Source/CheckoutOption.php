<?php

class EMS_Pay_Model_Source_CheckoutOption
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => EMS_Pay_Model_Config::CHECKOUT_OPTION_CLASSIC,
                'label' => Mage::helper('ems_pay')->__('Classic')
            ),
            array(
                'value' => EMS_Pay_Model_Config::CHECKOUT_OPTION_COMBINEDPAGE,
                'label' => Mage::helper('ems_pay')->__('Combined page')
            )
        );
    }
}
