<?php

class EMS_Pay_Block_Payment_Info extends Mage_Payment_Block_Info_Cc
{
    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     */
    public function getCcTypeName()
    {
        if (Mage::getModel('ems_pay/config')->isCreditCardMethod($this->getInfo()->getMethod())) {
            return parent::getCcTypeName();
        }
    }

    /**
     * Show name on card, expiration date and full cc number
     *
     * Expiration date and full number will show up only in secure mode (only for admin, not in emails or pdfs)
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $paymentInfo = Mage::getModel('ems_pay/info');

        if ($this->getInfo()->getCcLast4()) {
            $transport->addData([Mage::helper('payment')->__('Credit Card Number') => $this->getInfo()->getCcLast4()]);
        }

        if ($this->getIsSecureMode()) {
            $info = $paymentInfo->getPublicPaymentInfo($payment);
        } else {
            $info = $paymentInfo->getPaymentInfo($payment);
        }

        return $transport->addData($info);
    }
}