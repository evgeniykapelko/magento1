<?php

class EMS_Pay_Block_Payment_Form_Bancontact extends EMS_Pay_Block_Payment_Form_Form
{
    /**
     * @var EMS_Pay_Model_Config
     */
    protected $_config;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_config = Mage::getSingleton('ems_pay/config');
        $this->setTemplate('ems_pay/form/bancontact.phtml');
    }

}
