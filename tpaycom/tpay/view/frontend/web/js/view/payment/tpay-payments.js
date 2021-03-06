/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tpaycom_tpay',
                component: 'tpaycom_tpay/js/view/payment/method-renderer/tpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);