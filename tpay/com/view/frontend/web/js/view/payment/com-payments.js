/**
 * tpay_com Magento JS component
 *
 * @category    tpay
 * @package     tpay_com
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   tpay (http://tpay.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                type: 'tpay_com',
                component: 'tpay_com/js/view/payment/method-renderer/com-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);