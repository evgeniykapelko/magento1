<?php

class EMS_Pay_Model_Method_Sofort extends EMS_Pay_Model_Method_Abstract
{
    protected $_code = EMS_Pay_Model_Config::METHOD_SOFORT;

    /**
     * @inheritdoc
     */
    protected function _getCheckoutOption()
    {
        return EMS_Pay_Model_Config::CHECKOUT_OPTION_CLASSIC; //sofort supports only classic
    }
}
