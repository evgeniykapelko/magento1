<?php

abstract class EMS_Pay_Model_Method_Cc_Abstract extends EMS_Pay_Model_Method_Abstract
{
    /**
     * Name of field used in form
     *
     * @var string
     */
    protected $_cardTypeFieldName = '';

    /**
     * @inheritdoc
     */
    protected function _getPaymentMethod()
    {
        return $this->_getMethodCodeMapper()->getEmsCodeByMagentoCode($this->_getCardType());
    }

    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = parent::_getMethodSpecificRequestFields();
        $fields[EMS_Pay_Model_Info::AUTHENTICATE_TRANSACTION] = $this->_is3DSecureEnabled() ? 'true' : 'false';

        return $fields;
    }

    /**
     * Returns card type used for payment
     *
     * @return string|null
     */
    protected function _getCardType()
    {
        return $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable($quote = null)
    {
        $isAvailable = parent::isAvailable($quote);

        return $isAvailable && count($this->_getEnabledCardTypes()) > 0;
    }


    /**
     * @inheritdoc
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $cardType = $data->getData($this->_cardTypeFieldName); 
        if ($cardType) {
            $info->setAdditionalInformation($this->_cardTypeFieldName, $cardType);
            $info->setCcType(Mage::getModel('ems_pay/method_code_mapper')->getHumanReadableByMagentoCode($cardType));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        parent::validate();

        $errorMessage = '';
        $cardType = $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);

        if ($cardType === null || $cardType == '') {
            $errorMessage = $this->_helper->__('Card type is a required field');
        }

        if ($this->_validateCardType($cardType)) {
            $errorMessage = $this->_helper->__('Invalid card type selected');
        }

        if ($errorMessage !== '') {
            Mage::throwException($errorMessage);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function _is3DSecureEnabled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function addTransactionData(EMS_Pay_Model_Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);

        $info = $this->getInfoInstance();
        $info->setCcType($transactionResponse->getCcBrand());
        $info->setCcLast4($transactionResponse->getCcNumber());
        $info->setCcExpMonth($transactionResponse->getExpMonth());
        $info->setCcExpYear($transactionResponse->getExpYear());
        $info->setCcOwner($transactionResponse->getCcOwner());

        return $this;
    }

    /**
     * Validates whether card type code is valid
     *
     * @param string $code
     * @return bool
     */
    abstract protected function _validateCardType($code);

    /**
     * * Returns list of enabled credit card types
     *
     * @return array
     */
    abstract protected function _getEnabledCardTypes();
}
