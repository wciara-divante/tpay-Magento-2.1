<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.09.2016
 * Time: 15:40
 */
namespace tpay\com\Model;

use Magento\Payment\Helper\Data as PaymentHelper;

class blikHandler
{
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstance;
    /**
     * @var string
     */
    protected $methodCode = payment::CODE;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $amount;
    private $objectManager;
    protected $_checkoutSession;
    public $_storeManager;

    public function __construct(

        PaymentHelper $paymentHelper,
        \Magento\Checkout\Model\Session $checkoutSession,

        \Magento\Payment\Helper\Data $paymentData,

        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;


    }

    public function blikgo()
    {
        $blikcode = $_REQUEST["blikcode"];

        $api = $this->methodInstance->getConfigData('api');
        $kod = $this->methodInstance->getConfigData('api_key');
        $id = $this->methodInstance->getConfigData('vendor_id');
        $kwota = $this->_checkoutSession->getQuote()->getGrandTotal();
        $crc = 'asd';


        $postData = array();

        $postData['crc'] = $crc;
        $postData['kwota'] = $kwota;
        $postData['md5sum'] = md5($id . $kwota . $crc . $kod);

        $postData['api_password'] = $this->methodInstance->getConfigData('api_key2');
        $postData['akceptuje_regulamin'] = '1';
        $postData['kanal'] = '64';


        $url = 'https://secure.transferuj.pl/api/gw/' . $api . '/transaction/create';
        $xml = (new \SimpleXMLElement(blik::doCurlRequest($url, $postData)));


        if ((string)$xml->result[0] == '1') {


            $postData2 = array();

            $postData2['code'] = $blikcode;
            $postData2['title'] = (string)$xml->title[0];
            $postData2['api_password'] = $this->methodInstance->getConfigData('api_key2');

            $url = 'https://secure.transferuj.pl/api/gw/' . $api . '/transaction/blik';
            $respBlik = (new \SimpleXMLElement(blik::doCurlRequest($url, $postData2)));
            //sleep(10);
            $url = 'https://secure.transferuj.pl/api/gw/' . $api . '/transaction/get';
            $i = 0;
            $counter = 0;
            while ($i < 1) {

                $xml2 = (new \SimpleXMLElement(blik::doCurlRequest($url, array('title' => $postData2['title'], 'api_password' => $this->methodInstance->getConfigData('api_key2')))));

                $blik = array();

                $blik['status'] = (string)$xml2->status[0];

                $counter++;
                if ($blik['status'] == 'correct') {
                    $result = 'T1';
                    $i++;
                    exit ($result);
                } elseif ((string)$respBlik->result[0] == '0' && $blik['status'] == 'pending') {
                    $result = 'T2';
                    sleep(1);
                    if ($counter == 5) {
                        $i++;
                        exit ($result);
                    }

                } elseif ($blik['status'] == 'error') {
                    $result = 'T3';
                    $i++;
                    exit ($result);

                } elseif ($blik['status'] == 'chargeback') {
                    $i++;
                    $result = 'T4';
                    exit ($result);
                }

            }


        } else {
            $result = 'T5';
            exit ($result);
        }


    }
}