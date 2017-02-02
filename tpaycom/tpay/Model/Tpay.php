<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Model;

/**
 * Class Tpay
 * @package tpaycom\tpay\Model
 */
class Tpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'tpaycom_tpay';

    const CHANNEL = 'kanal';

    const BLIK_CODE = 'blik_code';

    const TERMS_ACCEPT = 'akceptuje_regulamin';

    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_comApi = false;

    protected $_countryFactory;

    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_availableCurrencyCodes = array('PLN');

    private $redirectURL = 'https://secure.tpay.com';

    private $termsURL = 'https://secure.tpay.com/regulamin.pdf';

    private $storeManager;

    private $objectManager;

    /** Min. order amount for BLIK level 0
     * @var float
     */

    private $minAmountBlik = 1.01;


    private $urlBuilder;

    /**
     * Tpay constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;
        $this->urlBuilder = $urlBuilder;
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }

    public function getCheckout()
    {
        return $this->objectManager->get('Magento\Checkout\Model\Session');
    }

    public function getRedirectURL()
    {
        return $this->redirectURL;
    }

    public function getMerchantId()
    {
        return (int) $this->getConfigData('merchant_id');
    }

    public function getSecurityCode()
    {
        return $this->getConfigData('security_code');
    }

    public function getApiKey()
    {
        return $this->getConfigData('api_key_tpay');
    }

    public function getApiPassword()
    {
        return $this->getConfigData('api_password');
    }

    public function showPaymentChannels()
    {
        return (bool)$this->getConfigData('show_payment_channels');
    }

    public function onlyOnlineChannels()
    {
        return (bool)$this->getConfigData('show_payment_channels_online');
    }

    public function getBlikLevelZeroStatus()
    {
        return (bool)$this->getConfigData('blik_level_zero');
    }


    /**Check that the BLIK Level 0 should be active on a payment channels list
     *
     * @return bool
     */

    public function checkBlikLevel0Settings()
    {
        if (!$this->showPaymentChannels() || !$this->getBlikLevelZeroStatus() || !$this->checkBlikAmount()) {
            return false;
        }

        $apiKey = $this->getApiKey();

        $apiPassword = $this->getApiPassword();

        if (empty($apiKey) || strlen($apiKey) < 8 || empty($apiPassword) || strlen($apiPassword) < 4) {
            return false;
        }

        return true;
    }

    /** Check that the  BLIK should be available for order/quote amount
     * @return bool
     */

    public function checkBlikAmount()
    {
        $amount = $this->getCheckout()->getQuote()->getBaseGrandTotal();

        if (!$amount) {
            $orderId = $this->getCheckout()->getLastRealOrderId();
            $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
            $amount = $order->getGrandTotal();
        }
        $amount = number_format($amount, 2);

        return (bool)($amount > $this->minAmountBlik);
    }

    /** Return url for  a tpay.com terms
     * @return string
     */

    public function getTermsURL()
    {
        return $this->termsURL;
    }

    /** Prepare payment data for tpay.com
     * @param null $orderId
     * @return array
     */

    public function getTpayFormData($orderId = null)
    {
        if ($orderId === null) {
            $orderId = $this->getCheckout()->getLastRealOrderId();
        }
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        $amount = number_format($order->getGrandTotal(), 2);
        $merchantId = $this->getMerchantId();
        $securityCode = $this->getSecurityCode();
        $crc = base64_encode($orderId);
        $md5sum = md5($merchantId . $amount . $crc . $securityCode);
        $name = $order->getBillingAddress()->getData('firstname'). ' '. $order->getBillingAddress()->getData('lastname');
        $data = [
            'id'           => $merchantId,
            'email'        => $order->getCustomerEmail(),
            'nazwisko'     => $name,
            'kwota'        => $amount,
            'opis'         => 'Zamówienie ' . $orderId,
            'md5sum'       => $md5sum,
            'crc'          => $crc,
            'adres'        => $order->getBillingAddress()->getData('street'),
            'miasto'       => $order->getBillingAddress()->getData('city'),
            'kod'          => $order->getBillingAddress()->getData('postcode'),
            'pow_url_blad' => $this->urlBuilder->getUrl('tpay/tpay/error'),
            'wyn_url'      => $this->urlBuilder->getUrl('tpay/tpay/notification'),
            'pow_url'      => $this->urlBuilder->getUrl('tpay/tpay/success'),
            'online'       => $this->onlyOnlineChannels() ? '1' : '0',
        ];

        return (array)$data;
    }

    /** Return url to redirect after placed order
     * @return string
     */

    public function getPaymentRedirectUrl()
    {
        return $this->urlBuilder->getUrl('tpay/tpay/redirect', ['uid' => time() . uniqid(true)]);
    }

    /** Check that tpay.com payments should be available.
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {

        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }
        if (!$this->getMerchantId() ||
            ($quote && !$this->isAvalilableForCurrency($quote->getCurrency()->getQuoteCurrencyCode()))) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function isAvalilableForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_availableCurrencyCodes)) {
            return false;
        }

        return true;
    }

    /** Assign additional data from order
     *
     * @param \Magento\Framework\DataObject $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $additionalData = $data->getData('additional_data');
        $info = $this->getInfoInstance();

        $info->setAdditionalInformation(
            static::CHANNEL,
            isset($additionalData[static::CHANNEL]) ? $additionalData[static::CHANNEL] : ''
        );
        $info->setAdditionalInformation(
            static::BLIK_CODE,
            isset($additionalData[static::BLIK_CODE]) ? $additionalData[static::BLIK_CODE] : ''
        );
        $info->setAdditionalInformation(
            static::TERMS_ACCEPT,
            isset($additionalData[static::TERMS_ACCEPT]) ? '1' : ''
        );

        return $this;
    }
}
