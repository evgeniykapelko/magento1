<?php

/**
 * Class EMS_Pay_Model_InvoiceMailer
 *
 * Sending Invoice through Magento's Mailer Queue.
 */
class EMS_Pay_Model_InvoiceMailer extends Mage_Sales_Model_Order_Invoice
{
    const INVOICE_ENTITY_TYPE = 'invoice';
    const EMAIL_EVENT_NAME_NEW_INVOICE = 'new_invoice';

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var Mage_Sales_Model_Order_Invoice
     */
    protected $_invoice;

    /**
     * @var EMS_Pay_Helper_Data
     */
    protected $_helper;


    public function __construct()
    {
        $this->_helper = Mage::helper('ems_pay');
    }

    /**
     * Send email by adding it to the mailer queue.
     *
     * @param bool $notifyCustomer
     * @param string $comment
     * @param bool $forceMode
     * @throws Exception
     */
    public function sendToQueue($notifyCustomer = true, $comment = '', $forceMode = false)
    {
        if(!$this->_order instanceof Mage_Sales_Model_Order ||
            !$this->_invoice instanceof Mage_Sales_Model_Order_Invoice) {
            throw new Exception($this->_helper->__('Order and Invoice object must be set.'));
        }

        $storeId = $this->_order->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewInvoiceEmail($storeId)) {
            throw new Exception($this->_helper->__('Cannot send Invoice Email for Invoice ID: '.$this->_invoice->getId()));
        }
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        if (!$notifyCustomer && !$copyTo) {
            throw new Exception($this->_helper->__('Cannot send Invoice Email for Invoice ID: '.$this->_invoice->getId().', no recipients added.'));
        }

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->_order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($this->_order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->_order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->_order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($this->_order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $this->_order,
                'invoice'      => $this->_invoice,
                'comment'      => $comment,
                'billing'      => $this->_order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->_invoice->getId())
            ->setEntityType(self::INVOICE_ENTITY_TYPE)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_INVOICE)
            ->setIsForceCheck(!$forceMode);

        $this->_invoice->setEmailSent(true);
        $this->_invoice->_getResource()->saveAttribute($this->_invoice, 'email_sent');

        $mailer->setQueue($emailQueue)->send();
    }

    /**
     * Set order object
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }

    /**
     * @param $invoice
     */
    public function setInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_invoice = $invoice;
    }

}