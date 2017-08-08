# EMS payments for Magento
Accept payments in Magento with the official EMS e-Commerce gateway plugin.

## Description
This plugin will add support for the following EMS payments methods to your Magento webshop:

* Credit card (Visa, Mastercard, Diner's club)
* PayPal
* iDEAL
* MasterPass
* Klarna
* Sofort
* Maestro, Maestro UK

## Provisioning

### Are you already a customer ?
If you are already registered as an EMS merchant then please enter the credentials and settings below.

For new customers please follow the link below to acquire an EMS merchant account.

### Becoming an EMS customer
Get a merchant account by sending an email with your request to integrations@emspay.eu

### Contact EMS Support
Visit the FAQ:
http://www.emspay.eu/en/customer-service/faq

Contact information:
https://www.emspay.eu/en/about-ems/contact

## Features
* Support for all available EMS payment methods
* Enable / disable payment methods
* Able to configure each payment method
* Toggle 3D secure transactions for the credit card payment method
* Switch between integration and production modes
* Select the pay mode of your preference (payonly, payplus, fullpay)
* Toggle payment method icons
* Transaction logs / notes in order
* IPN handling

## Installation

* Compatible with Magento 1.7.0.2 - 1.9.3.2

    ##### It's highly recommended to test the module on dev/staging environment before installing it on production and to backup you site's code and database before installing the module.

    ##### 1. Backup the website code and database
    You can use Magento backup function or any other tool you like

    ##### 2. Log in to magento admin panel
    
    ##### 3. Disable compilation
    Navigate to **System -> Tools -> Compilation** and disable the compilation if it's enabled.
    
    **It's important** to use **"Run Compilation Process** after enabling the compilation again when the module is installed.
    
    ##### 4. Flush magento cache
    
    ##### 5a. Manual installation
    Copy/upload the module files into your site's root directory
    
    ##### 5b. Installing with modman
    Use [modman](https://github.com/colinmollenhour/modman) to install the module
    
    ##### 6. Flush magento cache again
    
    ##### 7. Log out from magento admin panel and log in again


## Configuration

##### General Configuration
1. Log in to magento admin panel
2. Navigate to **System -> Configuration -> Payment Methods -> EMS Global Configuration** 
3. Choose operation mode
4. Enter Store name and Shared secret for chosen operation mode
5. Choose Checkout option

##### Configuration for individual payment methods
Review configuration options for individual payment methods and adjust it to meet your preferences
