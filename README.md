magento2-tpay_com
======================

com payment gateway Magento2 extension

**Module was made for educational purposes only, do not use in production! You can read the full article [here](http://tpay.net/magento-2/implementing-payment-gateway-magento-2/)**.

Other notes on extension: https://github.com/tpay/magento2-tpay_com/wiki/Notes/

Install
=======

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer config repositories.tpaycom git https://github.com/tpay/magento2-tpay_com.git
    composer require tpay/com:dev-master
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable tpay_com --clear-static-content
    php bin/magento setup:upgrade
    ```
4. Enable and configure com in Magento Admin under Stores/Configuration/Payment Methods/com

Other Notes
===========

**com works with USD only!** If USD is not your base currency, you will not see this module on checkout pages. This copndition is [hardcoded](https://github.com/tpay/magento2-tpay_com/blob/master/Model/Payment.php#L32) in payment model.

