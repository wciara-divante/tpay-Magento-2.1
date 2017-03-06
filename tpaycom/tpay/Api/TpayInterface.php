<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 01/02/17 10:25 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Api;

/**
 * Interface TpayInterface
 *
 * @package tpaycom\tpay\Api
 * @api
 */
interface TpayInterface
{
    /**
     * @var string
     */
    const CODE = 'tpaycom_tpay';

    /**
     * @var string
     */
    const CHANNEL = 'kanal';

    /**
     * @var string
     */
    const BLIK_CODE = 'blik_code';

    /**
     * @var string
     */
    const TERMS_ACCEPT = 'akceptuje_regulamin';

    /**
     * Return string for redirection
     *
     * @return string
     */
    public function getRedirectURL();

    /**
     * Return data for form
     *
     * @param null|int $orderId
     *
     * @return array
     */
    public function getTpayFormData($orderId = null);

    /**
     * @return string
     */
    public function getApiPassword();

    /**
     * @return string
     */
    public function getApiKey();

    /**
     * @return string
     */
    public function getSecurityCode();

    /**
     * @return int
     */
    public function getMerchantId();

    /**
     * Check that the BLIK Level 0 should be active on a payment channels list
     *
     * @return bool
     */
    public function checkBlikLevel0Settings();

    /**
     * @return bool
     */
    public function getBlikLevelZeroStatus();

    /**
     * @return bool
     */
    public function onlyOnlineChannels();

    /**
     * @return bool
     */
    public function showPaymentChannels();

    /**
     * Return url to redirect after placed order
     *
     * @return string
     */
    public function getPaymentRedirectUrl();

    /**
     * Return url for a tpay.com terms
     *
     * @return string
     */
    public function getTermsURL();
}
