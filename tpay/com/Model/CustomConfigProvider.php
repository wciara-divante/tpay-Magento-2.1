<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.09.2016
 * Time: 15:31
 */
namespace tpay\com\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\TestFramework\Event\Magento;
use tpay\com\Model\payment;
use \Magento\Payment\Model\Method;

use Magento\Payment\Helper\Data as PaymentHelper;
class CustomConfigProvider implements ConfigProviderInterface
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

    public function getConfig()
    {


        $tpay = $this->objectManager->create( 'tpay\com\Model\Payment' );


        $kod  = $this->methodInstance->getConfigData('api_key');
        $id = $this->methodInstance->getConfigData('vendor_id');
        $kwota = $this->_checkoutSession->getQuote()->getGrandTotal();
        $aqq=$tpay->test22();
     //   $adres=$tpay->capture($payment);
        $crc = 'asd';
        $config = [
            'payment' => [
                'customPayment' => [

                    'id' => $id,
                    'md5sum' =>md5($id.$kwota.$crc.$kod),
                    'kwota' => $kwota,
                    'crc' => $crc,
                    'opis' => 'zamówienie Magjento dwa!',

                    'wyn_url' => $aqq,

//                    'adres' => $billing->getStreetLine(1).$billing->getStreetLine(2),
//                    'miasto' => $billing->getCity(),
//                    'kod' => $billing->getPostcode(),
//                    'nazwisko'=>$billing->getLastname(),
//                    'imie'=>$billing->getFirstname(),
//                    'telefon'=>$billing->getTelephone(),


                ]
            ]
        ];



        return $config;
    }




}


