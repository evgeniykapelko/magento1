<?php

class EMS_Pay_Model_Method_Klarna extends EMS_Pay_Model_Method_Abstract
{
    protected $_code = EMS_Pay_Model_Config::METHOD_KLARNA;

    /**
     * Generates payment request fields specific for Klarna
     *
     * @return array
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = array();
        /** @var $billingAddress Mage_Sales_Model_Order_Address */
        $billingAddress = $this->_getOrder()->getBillingAddress();

        $fields[EMS_Pay_Model_Info::KLARNA_FIRSTNAME] = $billingAddress->getFirstname();
        $fields[EMS_Pay_Model_Info::KLARNA_LASTNAME] = $billingAddress->getLastname();
        $fields[EMS_Pay_Model_Info::KLARNA_STREET] = $billingAddress->getStreet1();
        $fields[EMS_Pay_Model_Info::KLARNA_PHONE] = $billingAddress->getTelephone();

        $fields = array_merge($fields, $this->_getCartRequestFields());

        return $fields;
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
            if (!$this->_currency->isCurrencySupportedByKlarna(
                $quote->getStore()->getBaseCurrencyCode(),
                $quote->getBillingAddress()->getCountry())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        $canUse = parent::canUseForCountry($country);

        return $canUse && $this->_getConfig()->isCountrySupportedByKlarna($country);
    }

    /**
     * @inheritdoc
     */
    protected function _getCheckoutOption()
    {
        return EMS_Pay_Model_Config::CHECKOUT_OPTION_CLASSIC; //klarna supports only classic
    }
}
