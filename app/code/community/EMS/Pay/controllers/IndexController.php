<?php

class EMS_Pay_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirects customer to payment gateway after the order was saved
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->setEmsQuoteId($session->getQuoteId());

        try {
            $this->loadLayout();
            $this->renderLayout();
            $session->unsQuoteId();
            $session->unsRedirectUrl();
        } catch (Exception $ex) {
            $session->setErrorMessage($ex->getMessage());
            $session->setCancelOrder(true);
            Mage::logException($ex);
            Mage::getSingleton('core/session')->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
            $this->_redirect('*/*/error');
        }

    }

    /**
     * Action used to restore quote if exception occurred while redirecting user to payment gateway if payment failed
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getEmsQuoteId(true));
        $message = $this->__('Order canceled because of error');
        if ($session->getErrorMessage() != '') {
            $message .= ': ' . $session->getErrorMessage();
            $session->unsErrorMessage();
        }

        if ($session->getLastRealOrderId()) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId() && $session->getCancelOrder() === true && $order->canCancel()) {
                $session->unsCancelOrder();
                $order->addStatusHistoryComment($message)
                    ->setIsCustomerNotified(false)
                    ->save();
                $order->cancel()
                    ->save();
            }
            Mage::helper('ems_pay/checkout')->restoreQuote();
        }

        $this->_redirect('checkout/onepage');
    }

    public function failAction()
    {
        Mage::log(__METHOD__, null, 'ems.log', true);
        Mage::log($this->getRequest()->getParams(), null, 'ems.log', true);

        $session = Mage::getSingleton('checkout/session');
        try {
            $response = $this->getEmsResponse();
            Mage::getSingleton('core/session')->addError($response->getFailReason());
            $session->setErrorMessage($response->getFailReason());
        } catch (Exception $ex) {
            $session->setErrorMessage($ex->getMessage());
            Mage::logException($ex);
            Mage::getSingleton('core/session')->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
        }

        $this->_redirect('*/*/error');
    }

    public function successAction()
    {
        Mage::log(__METHOD__, null, 'ems.log', true);
        Mage::log($this->getRequest()->getParams(), null, 'ems.log', true);

        try {
            /** @var EMS_Pay_Model_Response $response */
            $response = $this->getEmsResponse();
            if ($response->getTransactionStatus() === EMS_Pay_Model_Response::STATUS_WAITING) {
                Mage::getSingleton('core/session')->addSuccess($this->__('We are awaiting for payment confirmation.'));
            }

            $session = Mage::getSingleton('checkout/session');
            $session->setQuoteId($session->getEmsQuoteId(true));
            Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
            $this->_redirect('checkout/onepage/success', array('_secure'=>true));
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
    }

    public function ipnAction()
    {
        Mage::log(__METHOD__, null, 'ems.log', true);
        Mage::log($this->getRequest()->getParams(), null, 'ems.log', true);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        try {
            $data = $this->getRequest()->getPost();
            Mage::getModel('ems_pay/ipn')->processIpnRequest($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * @return EMS_Pay_Model_Response
     */
    private function getEmsResponse()
    {
        /** @var EMS_Pay_Model_Response $response */
        $response = Mage::getModel('ems_pay/response', $this->getRequest()->getParams());
        return $response;
    }
}
