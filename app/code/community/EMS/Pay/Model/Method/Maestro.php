<?php

class EMS_Pay_Model_Method_Maestro extends EMS_Pay_Model_Method_Cc_Abstract
{

    protected $_code = EMS_Pay_Model_Config::METHOD_MAESTRO;
    protected $_formBlockType = 'ems_pay/payment_form_maestro';

    /**
     * Name of field used in form
     *
     * @var string
     */
    protected $_cardTypeFieldName = 'debit_card_type';

    /**
     * @param string $code
     * @return bool
     */
    protected function _validateCardType($code)
    {
        return !$this->_getConfig()->isMaestroCardTypeCodeValid($code);
    }

    /**
     * Returns list of enabled card types
     *
     * @return array card names indexed by card code
     */
    protected function _getEnabledCardTypes()
    {
        return $this->_getConfig()->getMaestroCardTypes();
    }

    /**
     * @inheritdoc
     */
    protected function _is3DSecureEnabled()
    {
        return true; //Maestro requires 3D Secure to be enabled
    }
}
