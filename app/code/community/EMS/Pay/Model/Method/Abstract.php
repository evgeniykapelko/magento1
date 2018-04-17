<?php

abstract class EMS_Pay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    protected $_infoBlockType = 'ems_pay/payment_info';
    protected $_formBlockType = 'ems_pay/payment_form_form';

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment config instance
     *
     * @var EMS_Pay_Model_Config
     */
    protected $_config = null;

    /**
     * @var EMS_Pay_Model_Hash
     */
    protected $_hashHandler;

    /**
     * @var EMS_Pay_Model_Currency
     */
    protected $_currency;

    /**
     * @var EMS_Pay_Helper_Data
     */
    protected $_helper;

    /**
     * Depending on magento tax configuration discount may be applied on row total price.
     * EMS gateway expects to be given price for single item instead of row total if qty > 1
     * In some cases when qty for given product is > 1 rowTotal/qty results in price with 3 digits after decimal point
     * Prices with more than 2 digits after decimal point are not accepted by EMS.
     *
     * This array stores amounts used to round item prices that had 3 digits after decimal point that are used to
     * update chargetotal sent to EMS
     *
     * @var array
     */
    protected $_roundingAmounts = array();

    /**
     * Stores current index of cart item fields
     *
     * @var int
     */
    protected $_itemFieldsIndex = 1;

    public function __construct()
    {
        $this->_currency = Mage::getModel('ems_pay/currency');
        $this->_hashHandler = Mage::getModel('ems_pay/hash');
        $this->_helper = Mage::helper('ems_pay');
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('emspay/index/redirect', array('_secure' => true));
    }

    /**
     * Instantiate order state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     *
     * @return EMS_Pay_Model_Method_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);

        return $this;
    }

    /**
     * Returns payment action
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        /**
         * TODO check if really needed
         */
        return 'authorize';
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->_getConfig()->getGatewayUrl();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRedirectFormFields()
    {
        $debugData = array();
        $config = $this->_getConfig();

        try {
            $fields = array(
                EMS_Pay_Model_Info::TXNTYPE => $config->getTxnType(),
                EMS_Pay_Model_Info::TIMEZONE => $this->_getTimezone(),
                EMS_Pay_Model_Info::TXNDATETIME => $this->_getTransactionTime(),
                EMS_Pay_Model_Info::HASH_ALGORITHM => $this->_getHashAlgorithm(),
                EMS_Pay_Model_Info::HASH => $this->_getHash(),
                EMS_Pay_Model_Info::STORENAME => $this->_getStoreName(),
                EMS_Pay_Model_Info::MODE => $config->getDataCaptureMode(),
                EMS_Pay_Model_Info::CHECKOUTOPTION => $this->_getCheckoutOption(),
                EMS_Pay_Model_Info::CHARGETOTAL => $this->_getChargeTotal(),
                EMS_Pay_Model_Info::CURRENCY => $this->_getOrderCurrencyCode(),
                EMS_Pay_Model_Info::ORDER_ID => $this->_getOrderId(),
                EMS_Pay_Model_Info::PAYMENT_METHOD => $this->_getPaymentMethod(),
                EMS_Pay_Model_Info::RESPONSE_FAIL_URL => Mage::getUrl('emspay/index/fail', array('_secure' => true)),
                EMS_Pay_Model_Info::RESPONSE_SUCCESS_URL => Mage::getUrl('emspay/index/success', array('_secure' => true)),
                EMS_Pay_Model_Info::TRANSACTION_NOTIFICATION_URL => Mage::getUrl('emspay/index/ipn', array('_secure' => true)),
                EMS_Pay_Model_Info::LANGUAGE => $this->_getLanguage(),
                EMS_Pay_Model_Info::BEMAIL => $this->_getOrder()->getCustomerEmail(),
                EMS_Pay_Model_Info::MOBILE_MODE => $this->_getMobileMode(),
            );

            $fields = array_merge($fields, $this->_getAddressRequestFields());
            $fields = array_merge($fields, $this->_getMethodSpecificRequestFields());
            $this->_saveTransactionData();
        } catch (Exception $ex) {
            $debugData['exception'] = $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine();
            $this->_debug($debugData);
            throw $ex;
        }

        $debugData[] = $this->_helper->__('Generated redirect form fields');
        $debugData['redirect_form_fields'] = $fields;
        $this->_debug($debugData);

        return $fields;
    }

    /**
     * Generates payment request address fields
     *
     * @return array
     */
    protected function _getAddressRequestFields()
    {
        $fields = array();
        $order = $this->_getOrder();

        $billingAddress = $order->getBillingAddress();
        $fields[EMS_Pay_Model_Info::BCOMPANY] = $billingAddress->getCompany();
        $fields[EMS_Pay_Model_Info::BNAME] = $billingAddress->getName();
        $fields[EMS_Pay_Model_Info::BADDR1] = $billingAddress->getStreet1();
        $fields[EMS_Pay_Model_Info::BADDR2] = $billingAddress->getStreet2();
        $fields[EMS_Pay_Model_Info::BCITY] = $billingAddress->getCity();
        $fields[EMS_Pay_Model_Info::BSTATE] = $billingAddress->getRegion();
        $fields[EMS_Pay_Model_Info::BCOUNTRY] = $billingAddress->getCountry();
        $fields[EMS_Pay_Model_Info::BZIP] = $billingAddress->getPostcode();
        $fields[EMS_Pay_Model_Info::BPHONE] = $billingAddress->getTelephone();

        $shippingAddress = $order->getShippingAddress();
        $fields[EMS_Pay_Model_Info::SNAME] = $shippingAddress->getName();
        $fields[EMS_Pay_Model_Info::SADDR1] = $shippingAddress->getStreet1();
        $fields[EMS_Pay_Model_Info::SADDR2] = $shippingAddress->getStreet2();
        $fields[EMS_Pay_Model_Info::SCITY] = $shippingAddress->getCity();
        $fields[EMS_Pay_Model_Info::SSTATE] = $shippingAddress->getRegion();
        $fields[EMS_Pay_Model_Info::SCOUNTRY] = $shippingAddress->getCountry();
        $fields[EMS_Pay_Model_Info::SZIP] = $shippingAddress->getPostcode();

        return $fields;
    }

    /**
     * Generates cart related (items, shipping fee, discount) payment request fields
     *
     * @return array
     */
    protected function _getCartRequestFields()
    {
        $fields = array();
        $order = $this->_getOrder();

        $fields[EMS_Pay_Model_Info::SHIPPING] = EMS_Pay_Model_Info::CART_ITEM_SHIPPING_AMOUNT;
        $fields[EMS_Pay_Model_Info::VATTAX] = $this->_roundPrice($order->getBaseTaxAmount());
        $fields[EMS_Pay_Model_Info::SUBTOTAL] = $this->_getSubtotal();

        foreach ($order->getAllVisibleItems() as $item) {
            /** @var $item Mage_Sales_Model_Order_Item */
            $fields[EMS_Pay_Model_Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                $item->getId() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $item->getName() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                (int)$item->getQtyOrdered() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemPriceInclTax($item) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemPrice($item) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getItemTax($item) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                EMS_Pay_Model_Info::CART_ITEM_SHIPPING_AMOUNT;

            $this->_itemFieldsIndex++;
        }

        if ($this->_getRoundingAmount()) { //recalculate totals and hash
            $fields[EMS_Pay_Model_Info::CHARGETOTAL] = $this->_getChargeTotal();
            $fields[EMS_Pay_Model_Info::SUBTOTAL] = $this->_getSubtotal();
            $fields[EMS_Pay_Model_Info::HASH] = $this->_getHash();
        }

/* another approach of solving rounding issue - rounding amount added as separate cart issue
 * it's not used for now
        if ($this->getRoundingAmount()) {
            $fields[EMS_Pay_Model_Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                $this->getOrderId() . '_rounding' . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                'Rounding fee' . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                EMS_Pay_Model_Info::SHIPPING_QTY . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->getRoundingAmount() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->getRoundingAmount() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                0 . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                EMS_Pay_Model_Info::CART_ITEM_SHIPPING_AMOUNT;

            $this->_itemFieldsIndex++;
        }
*/
        $fields[EMS_Pay_Model_Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
            EMS_Pay_Model_Info::SHIPPING_FIELD_NAME . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            EMS_PAY_MODEL_INFO::SHIPPING_FIELD_LABEL . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            EMS_Pay_Model_Info::SHIPPING_QTY . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingInclTax()) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingAmount()) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            $this->_roundPrice($order->getBaseShippingTaxAmount()) . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
            EMS_Pay_Model_Info::CART_ITEM_SHIPPING_AMOUNT;
        ;
        $this->_itemFieldsIndex++;

        if ($this->_getDiscountInclTax() != 0) {
            $fields[EMS_Pay_Model_Info::CART_ITEM_FIELD_INDEX . $this->_itemFieldsIndex] =
                EMS_Pay_Model_Info::DISCOUNT_FIELD_NAME . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountLabel()  . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                EMS_Pay_Model_Info::SHIPPING_QTY . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountInclTax() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscount() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                $this->_getDiscountTaxAmount() . EMS_Pay_Model_Info::CART_ITEM_FIELD_SEPARATOR .
                EMS_Pay_Model_Info::CART_ITEM_SHIPPING_AMOUNT;
            ;
        }

        return $fields;
    }

    /**
     * Generates payment request fields specific for used method
     *
     * @return array
     */
    protected function _getMethodSpecificRequestFields()
    {
        return array();
    }

    /**
     * @return string
     */
    protected function _getHash()
    {
        return $this->_hashHandler->generateRequestHash(
            $this->_getTransactionTime(),
            $this->_getChargeTotal(),
            $this->_getOrderCurrencyCode()
        );
    }

    /**
     * @return string
     */
    protected function _getHashAlgorithm()
    {
        return $this->_hashHandler->getHashAlgorithm();
    }

    /**
     * Retrieves checkout option
     *
     * @return string
     */
    protected function _getCheckoutOption()
    {
        return $this->_getConfig()->getCheckoutOption();
    }

    /**
     * Retrieves payment method code used by ems based on magento code
     *
     * @return string
     */
    protected function _getPaymentMethod()
    {
        return $this->_getMethodCodeMapper()->getEmsCodeByMagentoCode($this->getCode());
    }

    /**
     * @return string
     */
    protected function _getStoreName()
    {
        return $this->_getConfig()->getStoreName();
    }

    /**
     * Retrieves timezone from order
     *
     * @return string
     */
    protected function _getTimezone()
    {
        return $this->_getOrder()->getCreatedAtStoreDate()->getTimezone();
    }

    /**
     * @return string
     */
    protected function _getTransactionTime()
    {
        $order = $this->_getOrder();

        return $order->getCreatedAtStoreDate()->toString(EMS_Pay_Model_Config::TXNDATE_ZEND_DATE_FORMAT);
    }

    /**
     * Retrieves amount to be charged from order
     *
     * @return float|string
     */
    protected function _getChargeTotal()
    {
        return $this->_roundPrice($this->_getOrder()->getBaseGrandTotal() + $this->_getRoundingAmount());
    }

    /**
     * @return float
     */
    protected function _getSubtotal()
    {
        $order = $this->_getOrder();
        return $this->_roundPrice($order->getBaseSubtotal() + $order->getBaseShippingAmount() + $this->_getDiscount() + $this->_getRoundingAmount());
    }

    /**
     * @return float
     */
    protected function _getDiscountInclTax()
    {
        return $this->_roundPrice($this->_getOrder()->getBaseDiscountAmount());
    }

    /**
     * @return float
     */
    protected function _getDiscount()
    {
        $order = $this->_getOrder();
        return $this->_roundPrice($this->_getDiscountInclTax() + $order->getBaseHiddenTaxAmount()); //discount is negative, hidden tax is positive number
    }

    /**
     * @return float
     */
    protected function _getDiscountTaxAmount()
    {
        return $this->_getDiscountInclTax() - $this->_getDiscount();
    }

    /**
     * @return string
     */
    protected function _getDiscountLabel()
    {
        return $this->_helper->__('Discount') . ' (' . $this->_getOrder()->getDiscountDescription() . ')';
    }

    /**
     * Returns language code for current store
     *
     * @return string
     */
    protected function _getLanguage()
    {
        return $this->_getConfig()->getLanguage();
    }

    /**
     * @return int|float
     */
    protected function _getRoundingAmount()
    {
        $amount = 0;
        foreach ($this->_roundingAmounts as $rounding) {
            $amount += $rounding;
        }

        return $amount;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return float|int
     */
    protected function _getItemPriceInclTax($item)
    {
        $qty = (int)$item->getQtyOrdered();
        $rowTotal = $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
        $price = $this->_roundPrice($rowTotal/$qty);
        $rowTotalAfterRounding = $price * $qty;
        if ($rowTotalAfterRounding != $rowTotal) {
            $this->_roundingAmounts[$item->getId()] =  round(100 * $rowTotalAfterRounding - 100 * $rowTotal) / 100;
        }

        return $price;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return float|int
     */
    protected function _getItemPrice($item)
    {
        return $this->_roundPrice($item->getBaseRowTotal()/(int)$item->getQtyOrdered());
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return float|int
     */
    protected function _getItemTax($item)
    {
        return $this->_getItemPriceInclTax($item) - $this->_getItemPrice($item);
    }

    /**
     * Retrieves mobile mode flag value
     *
     * @return string
     */
    protected function _getMobileMode()
    {
        return $this->_getConfig()->isMobileMode() ? 'true' : 'false';
    }

    /**
     * @return string
     */
    protected function _getOrderId()
    {
        return $this->_getOrder()->getIncrementId();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return Mage::helper('ems_pay/checkout')->getLastRealOrder();
    }

    /**
     * Retrieves currency numeric code required by EMS gateway
     *
     * @return int
     */
    protected function _getOrderCurrencyCode()
    {
        $order = $this->_getOrder();

        return $this->_currency->getNumericCurrencyCode($order->getBaseCurrency());
    }

    /**
     * Checks whether payment method can be used with specific currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_getConfig()->isCurrencySupported($currencyCode);
    }

    /**
     * Returns human readable payment method name
     *
     * @return string
     */
    protected function _getPaymentMethodName()
    {
        return Mage::getModel('ems_pay/method_code_mapper')->getHumanReadableByEmsCode($this->_getPaymentMethod());
    }

    /**
     * @return string
     */
    protected function _getTextCurrencyCode()
    {
        return Mage::getModel('ems_pay/currency')->getTextCurrencyCode($this->_getOrderCurrencyCode());
    }

    /**
     * Returns payment method logo file name
     *
     * @return string
     */
    public function getLogoFilename()
    {
        return $this->_getConfig()->getLogoFilename();
    }

    /**
     * Saves important information about the transaction for future use
     *
     * @return $this
     */
    protected function _saveTransactionData()
    {
        $data = array(
            EMS_Pay_Model_Info::CURRENCY => $this->_getTextCurrencyCode(),
            EMS_Pay_Model_Info::CHARGETOTAL => $this->_getChargeTotal(),
            EMS_Pay_Model_Info::TXNDATETIME => $this->_getTransactionTime(),
            EMS_Pay_Model_Info::HASH_ALGORITHM => $this->_getHashAlgorithm(),
            EMS_Pay_Model_Info::PAYMENT_METHOD => $this->_getPaymentMethodName(),
        );

        $info = $this->getInfoInstance();
        foreach ($data as $key => $value) {
            $info->setAdditionalInformation($key, $value);
        }

        $info->save();
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransactionTimeSentInTransactionRequest()
    {
        $info = $this->getInfoInstance();
        return $info->getAdditionalInformation(EMS_Pay_Model_Info::TXNDATETIME);
    }

    /**
     * @return string|null
     */
    public function getHashAlgorithmSentInTransactionRequest()
    {
        $info = $this->getInfoInstance();
        return $info->getAdditionalInformation(EMS_Pay_Model_Info::HASH_ALGORITHM);
    }

    /**
     * @inheritdoc
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            Mage::getModel('core/log_adapter', $this->_getConfig()->getLogFile())
                ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                ->log($debugData);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDebugFlag()
    {
        return $this->_getConfig()->isDebuggingEnabled();
    }

    /**
     * @return EMS_Pay_Model_Config
     */
    protected function _getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->getCode());
            if ($store = $this->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }

            $this->_config = Mage::getModel('ems_pay/config', $params);
        }

        return $this->_config;
    }

    /**
     * @return EMS_Pay_Model_Method_Code_Mapper
     */
    protected function _getMethodCodeMapper()
    {
        return Mage::getModel('ems_pay/method_code_mapper');
    }

    /**
     * @param $price
     * @return float
     */
    protected function _roundPrice($price)
    {
        return Mage::app()->getStore()->roundPrice($price);
    }

    /**
     * Adds transaction specific information to payment object.
     * It's ment to be overridden and used by classes that inherit from this one
     *
     * @param EMS_Pay_Model_Response $transactionResponse
     */
    public function addTransactionData (EMS_Pay_Model_Response $transactionResponse)
    {}
}
