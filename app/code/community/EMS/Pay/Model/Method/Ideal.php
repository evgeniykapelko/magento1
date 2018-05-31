<?php

class EMS_Pay_Model_Method_Ideal extends EMS_Pay_Model_Method_Abstract
{
    const ISSUING_BANK_FIELD_NAME = 'issuing_bank';
    const ISSUING_CUSTOMER_ID_FIELD_NAME = 'customerid';

    protected $_code = EMS_Pay_Model_Config::METHOD_IDEAL;
    protected $_formBlockType = 'ems_pay/payment_form_ideal';

    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = array();
        $fields[EMS_Pay_Model_Info::IDEAL_ISSUER_ID] = $this->_getIssuingBankCode();
        $fields[EMS_Pay_Model_Info::IDEAL_CUSTOMER_ID] = $this->_getCustomerid();

        return $fields;
    }

    /**
     * @return string|null
     */
    protected function _getIssuingBankCode()
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::ISSUING_BANK_FIELD_NAME);
    }

    /**
     * @return string|null
     */
    protected function _getCustomerid()
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::ISSUING_CUSTOMER_ID_FIELD_NAME);
    }

    /**
     * @inheritdoc
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        if ($data->getIssuingBank()) {
            $info->setAdditionalInformation(self::ISSUING_BANK_FIELD_NAME, $data->getIssuingBank());
        }

        if ($data->getCustomerid()) {
            $info->setAdditionalInformation(self::ISSUING_CUSTOMER_ID_FIELD_NAME, $data->getCustomerid());
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
            parent::validate();
        if (!$this->_isBankSelectionEnabled()) {
            return $this;
        }

        $errorMessage = '';
        $issuingBankCode = $this->getInfoInstance()->getAdditionalInformation(self::ISSUING_BANK_FIELD_NAME);

        if ($issuingBankCode === null || $issuingBankCode == '') {
            $errorMessage = $this->_helper->__('Issuing bank is a required field');
        }

        if ($this->_validateIssuingBankCode($issuingBankCode)) {
            $errorMessage = $this->_helper->__('Invalid issuing bank selected');
        }

        if ($errorMessage !== '') {
            Mage::throwException($errorMessage);
        }

        return $this;
    }

    /**
     * @param string $code
     * @return bool
     */
    protected function _validateIssuingBankCode($code)
    {
        return !$this->_getConfig()->isIdealIssuingBankCodeValid($code);
    }

    /**
     * @return bool
     */
    protected function _isBankSelectionEnabled()
    {
        return $this->_getConfig()->isIdealIssuingBankSelectionEnabled();
    }

    /**
     * @inheritdoc
     */
    public function addTransactionData(EMS_Pay_Model_Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation(EMS_Pay_Model_Info::ACCOUNT_OWNER_NAME, $transactionResponse->getAccountOwnerName());
        $info->setAdditionalInformation(EMS_Pay_Model_Info::IBAN, $transactionResponse->getIban());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        $isApplicable = parent::isApplicableToQuote($quote, $checksBitMask);
        if ($isApplicable === false) {
            return false;
        }

        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->_currency->isCurrencySupportedByIdeal($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }

        return true;
    }
}
