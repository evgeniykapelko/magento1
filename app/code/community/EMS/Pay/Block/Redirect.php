<?php

class EMS_Pay_Block_Redirect extends Mage_Core_Block_Template
{
    /**
     * @var EMS_Pay_Helper_Data
     */
    protected $_helper;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('ems_pay');
        $this->setTemplate('ems_pay/redirect.phtml');
    }

    /**
     * Retrieves redirect form action url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->_getPaymentMethod()->getGatewayUrl();
    }

    /**
     * @return array
     */
    public function getFormFields()
    {
        return $this->_getPaymentMethod()->getRedirectFormFields();
    }

    /**
     * @return EMS_Pay_Model_Method_Abstract
     * @throws Exception
     */
    protected function _getPaymentMethod()
    {
        $paymentMethod = $this->_getOrder()->getPayment()->getMethodInstance();
        if (!$paymentMethod instanceof EMS_Pay_Model_Method_Abstract) {
            Mage::throwException($this->_helper->__('Payment method %s is not supported', get_class($paymentMethod)));
        }

        return $paymentMethod;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return Mage::helper('ems_pay/checkout')->getLastRealOrder();
    }
}
