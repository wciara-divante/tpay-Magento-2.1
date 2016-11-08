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


        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        // 'Magento_Payment/js/view/payment/cc-form',
        //
        // 'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function ($,
              Component,
              placeOrderAction,
              selectPaymentMethodAction,
              customer,
              checkoutData,
              additionalValidators,
              url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'tpay_com/payment/com-form.html'

            },

            getCode: function() {
                return 'tpay_com';
            },


            getProperties:function () {

                var fields =
                    '<input type="hidden" name="id" value="' + window.checkoutConfig.payment.customPayment.id + '"/>' +
                    '<input type="hidden" name="kwota" value="' + window.checkoutConfig.payment.customPayment.kwota + '"/>' +
                    '<input type="hidden" name="opis" value="' + window.checkoutConfig.payment.customPayment.opis + '"/>' +
                    '<input type="hidden" name="crc" value="' + window.checkoutConfig.payment.customPayment.crc + '"/>' +
                    '<input type="hidden" name="md5sum" value="' + window.checkoutConfig.payment.customPayment.md5sum + '"/>' +
                    '<input type="hidden" name="imie" value="' +  window.checkoutConfig.payment.customPayment.imie + '"/>' +
                    '<input type="hidden" name="nazwisko" value="' + window.checkoutConfig.payment.customPayment.nazwisko + '"/>' +
                    '<input type="hidden" name="adres" value="' + window.checkoutConfig.payment.customPayment.adres + '"/>' +
                    '<input type="hidden" name="miasto" value="' + window.checkoutConfig.payment.customPayment.miasto + '"/>' +
                    '<input type="hidden" name="kod" value="' + window.checkoutConfig.payment.customPayment.telefon + '"/>' +
                    '<input type="hidden" name="telefon" value="' + window.checkoutConfig.payment.customPayment.telefon + '"/>' +
                    '<input type="hidden" name="email" value="' + window.checkoutConfig.payment.customPayment.email + '"/>' +
                    '<input type="hidden" name="wyn_url" value="' + window.checkoutConfig.payment.customPayment.wyn_url + '"/>' +
                    '';
                var container = jQuery("#fields");
                container.append(fields);


            },

            blikjs:function (str) {
                alert('jest');
                var xhttp;
                if (str.length == 1) {
                    document.getElementById("txtHint").innerHTML = "zero";
                    return;
                }
                xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("txtHint").innerHTML = this.responseText;
                    }
                };
                xhttp.open("GET", "blik.php?blikcode="+str, true);
                xhttp.send();
            },

            blikjs2:function () {

                $(document).on('submit', 'form.blik-form', function (e) {
                    e.preventDefault();

                    var actionurl = e.currentTarget.action;
                    var submit_btn = $(this).find('input[id=button_send]');
                    //alert(actionurl);
                    $.ajax({
                        url: actionurl,
                        type: 'post',
                        data: $(this).serialize(),
                        beforeSend: function() {


                            submit_btn.prop('disabled',true);
                            $('#button_send').val(process());

                        }
                    })
                        .always(function() {
                            submit_btn.prop('disabled',false);
                            submit_btn.val('Pay with Blik');

                        })
                        .done(function (data) {



                            if ((data)=="T2"){
                                document.getElementById("content").innerHTML = "<br/>{l s='You have provided wrong code or did not accepted transaction on time.' mod='transferuj'}<br/>" ;

                            }else if
                            ((data)=="T1") {
                                document.getElementById("content").innerHTML = "<br/>{l s='Transaction succesful' mod='transferuj'}<br/>";

                            }else if
                            ((data)=="T3") {
                                document.getElementById("content").innerHTML = "<br/>{l s='Transaction error, please check your account balance and limits' mod='transferuj'}<br/>";

                            }else if  ((data)=="T5") {
                                document.getElementById("content").innerHTML = "<br/>{l s='Unable to create transaction, configuration error!' mod='transferuj'}<br/>";
                            }else if  ((data)=="T4") {
                                document.getElementById("content").innerHTML = "<br/>{l s='Transaction returned' mod='transferuj'}<br/>";
                            }


                        })
                        .fail(function () {
                            alert("Error");
                        });


                });

                function process() {
                    document.getElementById("content").innerHTML = "<img SRC='https://e-services.nea.gov.sg/icare/Images/ProCircle.gif'/> {l s='Confirm your order now.' mod='transferuj'}";

                }

            },

            showChannels: function () {
                function ShowChannelsCombo()
                {
                    var str = '<div  style="margin:20px 0 15px 0"  id="kanal"></div>';

                    for (var i = 0; i < tr_channels.length; i++) {
                        str += '<div   class="channel" style="width:30%"  "><image id="' + tr_channels[i][0] + '" class="check" style="height: 80%" src="' + tr_channels[i][3] + '" ></div>';
                    }

                    var container = jQuery("#kanaly_v");
                    container.append(str);

                    $(document).ready(function () {
                        jQuery(".check").click(function () {
                            $(".check").removeClass("checked_v");
                            $(this).addClass("checked_v");
                            $("html,body").animate({scrollTop: 1600}, 600);
                            var kanal = 0;
                            kanal = $(this).attr("id");
                            $('#channel').val(kanal);

                        });

                    });
                    jQuery("form[name=tpay_com-form]").submit(function (e) {

                        if ($('#channel').attr("value") == " ") {


                            alert("Wybierz bank");
                            return false;

                        }
                        else {
                            return true;
                        }
                    });

                }
               jQuery.getScript("https://secure.tpay.com/channels-"+window.checkoutConfig.payment.customPayment.id+"0.js", function () {

                    ShowChannelsCombo()
                });
            },

            isActive: function() {
                return true;
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },

            afterPlaceOrder: function () {
                window.location.replace("../com/Processing/pay");
            },
            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            }

        });
    }
);
