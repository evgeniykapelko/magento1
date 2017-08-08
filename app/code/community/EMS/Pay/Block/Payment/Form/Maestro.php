<?php

class EMS_Pay_Block_Payment_Form_Maestro extends EMS_Pay_Block_Payment_Form_Form
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ems_pay/form/maestro.phtml');
    }

    /**
     * Returns card type selected by customer
     *
     * @return string|null
     */
    public function getSelectedCardType()
    {
        return $this->getMethod()->getInfoInstance()->getAdditionalInformation('debit_card_type');
    }

    /**
     * Returns list of supported card types
     *
     * @return array card names indexed by card code
     */
    public function getCardTypes()
    {
        return Mage::getModel('ems_pay/config')->getMaestroCardTypes();
    }
}
