<?php

class EMS_Pay_Block_Payment_Form_Cc extends EMS_Pay_Block_Payment_Form_Form
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ems_pay/form/cc.phtml');
    }

    /**
     * Returns card type selected by customer
     *
     * @return string|null
     */
    public function getSelectedCardType()
    {
        return $this->getMethod()->getInfoInstance()->getAdditionalInformation('ems_card_type');
    }

    /**
     * Returns list of supported credit card types
     *
     * @return array card names indexed by card code
     */
    public function getCardTypes()
    {
        return Mage::getModel('ems_pay/config')->getEnabledCreditCardTypes();
    }

    /**
     * Returns logo path for specified card type
     *
     * @param string $cardType
     * @return string
     */
    public function getCardLogoPath($cardType)
    {
        $fileName = Mage::getSingleton('ems_pay/config')->getLogoFilename($cardType);
        return $this->getSkinUrl('images/ems_pay/icons/') . $fileName;
    }
}
