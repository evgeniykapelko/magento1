<?php

class EMS_Pay_Block_Payment_Form_Ideal extends EMS_Pay_Block_Payment_Form_Form
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
        $this->setTemplate('ems_pay/form/ideal.phtml');
    }

    /**
     * Returns bank selected by customer
     *
     * @return string|null
     */
    public function getIssuingBank()
    {
        return $this->getMethod()->getInfoInstance()->getAdditionalInformation('issuing_bank');
    }

    /**
     * Returns iDeal Customer Id
     *
     * @return string|null
     */
    public function getCustimerid()
    {
        return $this->getMethod()->getInfoInstance()->getAdditionalInformation('customerid');
    }

    /**
     * Returns list of supported issuing banks
     *
     * @return array bank names indexed by bank code
     */
    public function getIssuingBanks()
    {
        return $this->_config->getIdealIssuingBanks();
    }

    /**
     * @return bool
     */
    public function isIssuingBankSelectionEnabled()
    {
        return $this->_config->isIdealIssuingBankSelectionEnabled();
    }

    /**
     * @return bool
     */
    public function isCustomerIdSelectionEnabled()
    {
        return $this->_config->isIdealCustomerIdSelectionEnabled();
    }
}
