<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */
namespace tpaycom\tpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\TestFramework\Event\Magento;
use tpaycom\tpay\Model\Tpay;
use \Magento\Payment\Model\Method;

use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class TpayConfigProvider
 * @package tpaycom\tpay\Model
 */

class TpayConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    private $methodInstance;
    /**
     * @var string
     */
    private $methodCode = Tpay::CODE;
    /**
     * @var \Magento\Checkout\Model\Session
     */

    private $objectManager;

    private $checkoutSession;

    public $storeManager;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    public function getConfig()
    {
        $tpay = $this->methodInstance;

        $config = [
            'tpay' => [
                'payment' => [
                    'redirectUrl'         => $tpay->getPaymentRedirectUrl(),
                    'tpayLogoUrl'         => $this->getTpayLogoUrlUrl(),
                    'merchantId'          => $tpay->getMerchantId(),
                    'showPaymentChannels' => $this->showChannels(),
                    'getTerms'            => $this->getTerms(),
                    'addCSS'              => $this->addCSS(),
                    'blikStatus'          => $this->blikStatus(),
                    'onlyOnlineChannels'  => $this->onlyOnlineChannels(),
                    'getBlikChannelID'    => $this->getBlikChannelID(),
                    'getBlikPaymentLogo'  => $this->getBlikPaymentLogo(),
                ]
            ]
        ];

        return $tpay->isAvailable() ? $config : [];
    }

    public function showChannels()
    {
        if ($this->methodInstance->showPaymentChannels()) {
            $script = 'tpaycom_tpay::js/render_channels.js';

            return $this->createScript($script);
        }
    }

    public function blikStatus()
    {
        return $this->methodInstance->checkBlikLevel0Settings();
    }

    public function onlyOnlineChannels()
    {
        return $this->methodInstance->onlyOnlineChannels();
    }

    public function getBlikChannelID()
    {
        return Transaction::BLIK_CHANNEL;
    }

    public function addCSS()
    {
        $css = 'tpaycom_tpay::css/tpay.css';

        return $this->createCSS($css);
    }

    public function getTerms()
    {
        if ($this->methodInstance->showPaymentChannels()) {
            $textAcceptTerms = __('Akceptuję regulamin tpay.com');

            return <<<HTML
        <div style="margin: 15px 0 0 0; text-align: center">
               <input  type="checkbox"  checked name="akceptuje_regulamin" id="akceptuje_regulamin" />
               <label for="akceptuje_regulamin">
               <a target="_blank" href="{$this->methodInstance->getTermsURL()}">{$textAcceptTerms}</a>.</label></div>
HTML;
        }
    }

    public function createScript($script)
    {
        return <<<HTML
            <script type="text/javascript">
                require(['jquery'], function ($) {
                    $.getScript('{$this->generateURL($script)}');

                });
            </script>
HTML;
    }

    public function createCSS($css)
    {
        return <<<HTML
             <link rel="stylesheet" type="text/css" href="{$this->generateURL($css)}">

HTML;
    }

    public function getTpayLogoUrlUrl()
    {
        return $this->generateURL('tpaycom_tpay::images/logo_tpay.png');
    }

    public function getBlikPaymentLogo()
    {
        return $this->generateURL('tpaycom_tpay::images/blik_payment.png');
    }

    public function generateURL($name)
    {
        $assetRepository = $this->objectManager->get('Magento\Framework\View\Asset\Repository');

        return $assetRepository->createAsset($name)->getUrl();
    }
}
