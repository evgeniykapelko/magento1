<?php

class EMS_Pay_Model_Method_Bancontact extends EMS_Pay_Model_Method_Abstract
{
    protected $_code = EMS_Pay_Model_Config::METHOD_BANCONTACT;
    protected $_formBlockType = 'ems_pay/payment_form_bancontact';


    /**
     * @param string $code
     * @return bool
     */
    protected function _validateIssuingBankCode($code)
    {
        return !$this->_getConfig()->isBancontactIssuingBankCodeValid($code);
    }


    /**
     * @inheritdoc
     */
    public function addTransactionData(EMS_Pay_Model_Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);

        $info = $this->getInfoInstance();
        /** @var TYPE_NAME $info */
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
