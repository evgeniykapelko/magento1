<?php

class EMS_Pay_Model_Info
{
    const TXNTYPE = 'txntype';
    const TIMEZONE = 'timezone';
    const TXNDATETIME = 'txndatetime';
    const HASH_ALGORITHM = 'hash_algorithm';
    const HASH = 'hash';
    const STORENAME = 'storename';
    const MODE = 'mode';
    const CHARGETOTAL = 'chargetotal';
    const CHECKOUTOPTION = 'checkoutoption';
    const SHIPPING = 'shipping';
    const VATTAX = 'vattax';
    const SUBTOTAL = 'subtotal';
    const CURRENCY = 'currency';
    const ORDER_ID = 'oid';
    const TDATE = 'tdate';
    const PAYMENT_METHOD = 'paymentMethod';
    const LANGUAGE = 'language';
    const MOBILE_MODE = 'mobileMode';
    const AUTHENTICATE_TRANSACTION = 'authenticateTransaction';
    const RESPONSE_FAIL_URL = 'responseFailURL';
    const RESPONSE_SUCCESS_URL = 'responseSuccessURL';
    const TRANSACTION_NOTIFICATION_URL = 'transactionNotificationURL';

    const APPROVAL_CODE = 'approval_code';
    const REFNUMBER = 'refnumber';
    const STATUS = 'status';
    const FAIL_REASON = 'fail_reason';
    const RESPONSE_HASH = 'response_hash';
    const NOTIFICATION_HASH = 'notification_hash';
    const PROCESSOR_RESPONSE_CODE = 'processor_response_code';
    const CC_COUNTRY = 'cccountry';
    const CC_BRAND = 'ccbrand';
    const CC_OWNER = 'bname';
    const CC_NUMBER = 'cardnumber';
    const CC_EXP_YEAR = 'expyear';
    const CC_EXP_MONTH = 'expmonth';

    const BCOMPANY = 'bcompany';
    const BNAME = 'bname';
    const BADDR1 = 'baddr1';
    const BADDR2 = 'baddr2';
    const BCITY = 'bcity';
    const BSTATE = 'bstate';
    const BCOUNTRY = 'bcountry';
    const BZIP = 'bzip';
    const BPHONE = 'phone';
    const BEMAIL = 'email';

    const SNAME = 'sname';
    const SADDR1 = 'saddr1';
    const SADDR2 = 'saddr2';
    const SCITY = 'scity';
    const SSTATE = 'sstate';
    const SCOUNTRY = 'scountry';
    const SZIP = 'szip';

    const IPG_TRANSACTION_ID = 'ipgTransactionId';
    const ENDPOINT_TRANSACTION_ID = 'endpointTransactionId';

    const KLARNA_FIRSTNAME = 'klarnaFirstname';
    const KLARNA_LASTNAME = 'klarnaLastname';
    const KLARNA_STREET = 'klarnaStreetName';
    const KLARNA_PHONE = 'klarnaPhone';

    const ACCOUNT_OWNER_NAME = 'accountOwnerName';
    const IBAN = 'iban';

    const IDEAL_ISSUER_ID = 'idealIssuerID';
    const IDEAL_CUSTOMER_ID = 'customerID';

    const BANCONTACT_ISSUER_ID = 'bancontactIssuerID';

    const CART_ITEM_FIELD_INDEX = 'item';
    const CART_ITEM_FIELD_SEPARATOR = ';';
    const CART_ITEM_SHIPPING_AMOUNT = 0;
    const DISCOUNT_FIELD_NAME = 'IPG_DISCOUNT';
    const SHIPPING_FIELD_NAME = 'IPG_SHIPPING';
    const SHIPPING_FIELD_LABEL = 'IPG_SHIPPING';
    const SHIPPING_QTY = 1;

    /**
     * It's not clear yet which response field should be used as transaction ip so this constant is a placeholder
     */
    const TRANSACTION_ID = self::TDATE;

    /**
     * Payment info fields that are public (can be displayed to customer
     *
     * @var array
     */
    protected $_publicPaymentInfoFields = array(
        self::ACCOUNT_OWNER_NAME
    );

    /**
     * @var array
     */
    protected $_paymentInfoFields = array(
        self::CHARGETOTAL,
        self::CURRENCY,
        self::PAYMENT_METHOD,
        self::TRANSACTION_ID,
        self::APPROVAL_CODE,
        self::REFNUMBER,
        self::IBAN,
        self::PROCESSOR_RESPONSE_CODE,
        self::IPG_TRANSACTION_ID,
        self::ENDPOINT_TRANSACTION_ID
    );

    /**
     * @param Mage_Payment_Model_Info $payment
     * @return array
     */
    public function getPublicPaymentInfo(Mage_Payment_Model_Info $payment)
    {
        return $this->_getPaymentInfoFields($this->_publicPaymentInfoFields, $payment);
    }

    /**
     * @param Mage_Payment_Model_Info $payment
     * @return array
     */
    public function getPaymentInfo(Mage_Payment_Model_Info $payment)
    {
        return $this->_getPaymentInfoFields(array_merge($this->_paymentInfoFields, $this->_publicPaymentInfoFields), $payment);
    }

    /**
     * @param $fields
     * @param Mage_Payment_Model_Info $payment
     * @return array
     */
    protected function _getPaymentInfoFields($fields, Mage_Payment_Model_Info $payment)
    {
        $info = array();
        foreach ($fields as $field)
        {
            if ($payment->hasAdditionalInformation($field)) {
                $info[$this->_getFieldLabel($field)] = $payment->getAdditionalInformation($field);
            }
        }

        return $info;
    }

    /**
     * Retrieves payment info field label
     * 
     * @param string $field
     * @return string
     */
    protected function _getFieldLabel($field)
    {
        $helper = Mage::helper('ems_pay');
        $_fieldLabels = array(
            self::CHARGETOTAL => $helper->__('Amount'),
            self::CURRENCY => $helper->__('Currency'),
            self::PAYMENT_METHOD => $helper->__('Payment method'),
            self::APPROVAL_CODE => $helper->__('Approval code'),
            self::REFNUMBER => $helper->__('Reference number'),
            self::STATUS => $helper->__('Status'),
            self::TRANSACTION_ID => $helper->__('Transaction id'),
            self::ACCOUNT_OWNER_NAME => $helper->__('Account owner name'),
            self::IBAN => $helper->__('Iban'),
            self::IPG_TRANSACTION_ID => $helper->__('Ipg transaction id'),
            self::ENDPOINT_TRANSACTION_ID => $helper->__('Endpoint transaction id'),
            self::PROCESSOR_RESPONSE_CODE => $helper->__('Processor response code'),
        );

        return isset($_fieldLabels[$field]) ? $_fieldLabels[$field] : '';
    }
}
