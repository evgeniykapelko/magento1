<?php

class EMS_Pay_Helper_Checkout extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->_getQuote($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $this->_getCheckoutSession()
                    ->replaceQuote($quote)
                    ->unsLastRealOrderId();
                return true;
            }
        }
        return false;
    }

    /**
     * Get order instance based on last order ID
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function getLastRealOrder()
    {
        $orderId = $this->_getCheckoutSession()->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }

        $this->_order = Mage::getModel('sales/order');
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }

        if (!$this->_order->getId()) {
            throw new Exception(Mage::helper('ems_pay')->__('Order for id %s not found', $orderId));
        }

        return $this->_order;
    }

    /**
     * Return checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return sales quote instance for specified ID
     *
     * @param int $quoteId Quote identifier
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }
}