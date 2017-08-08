<?php

class EMS_Pay_Model_Source_CheckoutOption
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => EMS_Pay_Model_Config::CHECKOUT_OPTION_CLASSIC,
                'label' => Mage::helper('ems_pay')->__('Classic')
            ],
            [
                'value' => EMS_Pay_Model_Config::CHECKOUT_OPTION_COMBINEDPAGE,
                'label' => Mage::helper('ems_pay')->__('Combined page')
            ]
        ];
    }
}
