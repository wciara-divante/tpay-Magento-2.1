<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 07/02/17 11:06 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Model\MethodInterface;
use tpaycom\tpay\Api\TpayInterface;

use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class TpayConfigProvider
 *
 * @package tpaycom\tpay\Model
 */
class TpayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var TpayInterface
     */
    protected $paymentMethod;

    /**
     * TpayConfigProvider constructor.
     *
     * @param PaymentHelper $paymentHelper
     * @param Repository    $assetRepository
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Repository $assetRepository
    ) {
        $this->assetRepository = $assetRepository;
        $this->paymentHelper   = $paymentHelper;
    }

    /**
     * @return TpayInterface|MethodInterface
     */
    protected function getPaymentMethodInstance()
    {
        if (null === $this->paymentMethod) {
            $this->paymentMethod = $this->paymentHelper->getMethodInstance(TpayInterface::CODE);
        }

        return $this->paymentMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $tpay = $this->getPaymentMethodInstance();

        $config = [
            'tpay' => [
                'payment' => [
                    'redirectUrl'         => $tpay->getPaymentRedirectUrl(),
                    'tpayLogoUrl'         => $this->generateURL('tpaycom_tpay::images/logo_tpay.png'),
                    'merchantId'          => $tpay->getMerchantId(),
                    'showPaymentChannels' => $this->showChannels(),
                    'getTerms'            => $this->getTerms(),
                    'addCSS'              => $this->createCSS('tpaycom_tpay::css/tpay.css'),
                    'blikStatus'          => $this->getPaymentMethodInstance()->checkBlikLevel0Settings(),
                    'onlyOnlineChannels'  => $this->getPaymentMethodInstance()->onlyOnlineChannels(),
                    'getBlikChannelID'    => Transaction::BLIK_CHANNEL,
                    'getBlikPaymentLogo'  => $this->generateURL('tpaycom_tpay::images/blik_payment.png'),
                ],
            ],
        ];

        return $tpay->isAvailable() ? $config : [];
    }

    /**
     * @return string|null
     */
    public function showChannels()
    {
        if ($this->getPaymentMethodInstance()->showPaymentChannels()) {
            $script = 'tpaycom_tpay::js/render_channels.js';

            return $this->createScript($script);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTerms()
    {
        if ($this->getPaymentMethodInstance()->showPaymentChannels()) {
            $textAcceptTerms = __('Akceptuję regulamin tpay.com');

            return "
            <div style=\"margin: 15px 0 0 0; text-align: center\">
                <input  type=\"checkbox\"  checked name=\"akceptuje_regulamin\" id=\"akceptuje_regulamin\" />
                <label for=\"akceptuje_regulamin\">
                    <a target=\"_blank\" href=\"{$this->getPaymentMethodInstance()->getTermsURL()}\">{$textAcceptTerms}</a>.
                </label>
            </div>";
        }

        return null;
    }

    /**
     * @param string $script
     *
     * @return string
     */
    public function createScript($script)
    {
        return "
            <script type=\"text/javascript\">
                require(['jquery'], function ($) {
                    $.getScript('{$this->generateURL($script)}');

                });
            </script>";
    }

    /**
     * @param string $css
     *
     * @return string
     */
    public function createCSS($css)
    {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->generateURL($css)}\">";
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function generateURL($name)
    {
        return $this->assetRepository->createAsset($name)->getUrl();
    }
}
