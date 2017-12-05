<?php

class EMS_Pay_Model_Method_Bancontact extends EMS_Pay_Model_Method_Abstract
{
    const ISSUING_BANK_FIELD_NAME = 'issuing_bank';

    protected $_code = EMS_Pay_Model_Config::METHOD_BANCONTACT;
    protected $_formBlockType = 'ems_pay/payment_form_bancontact';

    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = [];
        $fields[EMS_Pay_Model_Info::BANCONTACT_ISSUER_ID] = $this->_getIssuingBankCode();

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
     * @inheritdoc
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        if ($data->getIssuingBank()) {
            $info->setAdditionalInformation(self::ISSUING_BANK_FIELD_NAME, $data->getIssuingBank());
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
        return !$this->_getConfig()->isBancontactIssuingBankCodeValid($code);
    }

    /**
     * @return bool
     */
    protected function _isBankSelectionEnabled()
    {
        return $this->_getConfig()->isBancontactIssuingBankSelectionEnabled();
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
            if (!$this->_currency->isCurrencySupportedByBancontact($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }

        return true;
    }

    public function canUseForCountry($country)
    {
        $canUse = parent::canUseForCountry($country);

        return $canUse && $this->_getConfig()->isCountrySupportedByBancontact($country);
    }
}
