<?php

class EMS_Pay_Block_Payment_Form_Form extends Mage_Payment_Block_Form
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ems_pay/form/form.phtml');
    }

    /**
     * Returns payment method logo path
     *
     * @return string
     */
    public function getLogoPath()
    {
        return $this->getSkinUrl('images/ems_pay/icons/' . $this->getMethod()->getLogoFilename());
    }
}