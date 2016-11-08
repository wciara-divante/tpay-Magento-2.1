<?php
/**
 * com payment method model
 *
 * @category    tpay
 * @package     tpay_com
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   tpay (http://tpay.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace tpay\com\Model;

class payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'tpay_com';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;

    protected $_comApi = false;

    protected $_countryFactory;

    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('PLN');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    public $_storeManager;
    private $objectManager;
    protected $_factory;

    /**
     * List of shared instances
     *
     * @var array
     */
    protected $_sharedInstances = [];


    protected $_config;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,

        array $data = array()
    ) {

        $this->_storeManager = $storeManager;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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
//
//        $this->_comApi = $com;
//        $this->_comApi->setApiKey(
//            $this->getConfigData('api_key')
//        );
//
//        $this->_minAmount = $this->getConfigData('min_order_total');
//        $this->_maxAmount = $this->getConfigData('max_order_total');
    }
    public function getCheckout()
    {
        return $this->objectManager->get('Magento\Checkout\Model\Session');
    }
    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //throw new \Magento\Framework\Validator\Exception(__('Inside com, throwing donuts :]'));


        /** @var \Magento\Sales\Model\Order $order */
        $order_id=$order->getRealOrderId();
        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();
        if (is_null($order_id)) {
            $order_id = $this->getCheckout()->getLastRealOrderId();
        }
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order_id);

        try {
            $requestData = [
            'id' => $this->getConfigData('vendor_id'),

            'opis' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
            'crc'=>$order_id,
            'md5sum'=>md5($this->getConfigData('vendor_id').$order_id.$this->getConfigData('api_key')),
            'adres' => $billing->getStreetLine(1).$billing->getStreetLine(2),
            'miasto' => $billing->getCity(),
            'kod' => $billing->getPostcode(),
            'nazwisko'=>$billing->getLastname(),
            'imie'=>$billing->getFirstname(),
            'telefon'=>$billing->getTelephone(),

            ];

            $charge = \Stripe\Charge::create($requestData);
            $payment
                ->setTransactionId($charge->id)
                ->setIsTransactionClosed(0);

        } catch (\Exception $e) {

            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }

        return $this;
    }

    public function test22(){


        $testowa = 'miasto';


        return $testowa;
    }



    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */


    /**
     * Determine method availability based on quote amount and config data
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

        if (!$this->getConfigData('api_key')) {
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
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
}

class blik
{

    private static $curlInfo;
    /**
     * Last executed cURL error
     * @var string
     */
    private static $curlError = '';

    /**
     * Last executed cURL errno
     * @var string
     */
    private static $curlErrno = '';
    /**
     * Get last info
     *
     * @return mixed
     */
    /**
     * List of http response codes the occurrence of which results in throw exception
     *
     * @var array
     */
    private static $httpCodes = array(
        500 => '500: Internal Server Error',
        501 => '501: Not Implemented',
        502 => '502: Bad Gateway',
        503 => '503: Service Unavailable',
        504 => '504: Gateway Timeout',
        505 => '505: HTTP Version Not Supported',
    );
    /**
     * Check cURL response and throw exception if code is not allowed
     *
     * @throws TException
     */
    private static function checkResponse()
    {
        $responseCode = self::$curlInfo['http_code'];
        if ($responseCode !== 200){
            if (isset(self::$httpCodes[$responseCode])){
                throw new TException(sprintf('Transferuj.pl server return %s', self::$httpCodes[$responseCode]));
            } else {
                throw new TException('Unexpected response from Transferuj server:'. $responseCode);
            }
        }
    }

    public static function doCurlRequest($url, $postData = array())
    {
        if (!function_exists('curl_init') || !function_exists('curl_exec')) {
            throw new TException('cURL function not available');
        }



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $curlRes = curl_exec($ch);

        self::$curlInfo = curl_getinfo($ch);
        self::$curlError = curl_error($ch);
        self::$curlErrno = curl_errno($ch);

        //self::checkResponse();

        curl_close($ch);





        return $curlRes;
    }


}