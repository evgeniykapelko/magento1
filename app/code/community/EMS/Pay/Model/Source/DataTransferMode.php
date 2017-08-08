<?php

class EMS_Pay_Model_Source_DataTransferMode
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => EMS_Pay_Model_Config::DATA_TRANSFER_PAYONLY,
                'label' => Mage::helper('ems_pay')->__('Payment information')
            ],
            [
                'value' => EMS_Pay_Model_Config::DATA_TRANSFER_PAYPLUS,
                'label' => Mage::helper('ems_pay')->__('Payment information + billing')
            ],
            [
                'value' => EMS_Pay_Model_Config::DATA_TRANSFER_FULLPAY,
                'label' => Mage::helper('ems_pay')->__('Payment information + billing + shipping')
            ]
        ];
    }
}
