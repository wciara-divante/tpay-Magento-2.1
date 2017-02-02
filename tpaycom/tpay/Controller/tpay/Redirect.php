<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Controller\tpay;

use Magento\Framework\Exception\CouldNotSaveException;
use tpaycom\tpay\Model\Transaction;

/**
 * Class Redirect
 * @package tpaycom\tpay\Controller\tpay
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    private $tpay;

    private $request;

    /**
     * Redirect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \tpaycom\tpay\Model\Tpay $tpayModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->request = $request;

        parent::__construct($context);
        $this->tpay = $tpayModel;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $uid = $this->request->getParam('uid');

        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $orderId = $session->getLastRealOrderId();

        if (!$orderId || !$uid) {
            $this->_redirect('checkout/cart');

            return;
        }

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
        $paymentData = $order->getPayment()->getData();

        $additionalPaymentInformation = $paymentData['additional_information'];

        if (!empty($additionalPaymentInformation['blik_code'])
            && $this->tpay->checkBlikLevel0Settings() &&
            $additionalPaymentInformation['kanal'] == Transaction::BLIK_CHANNEL
        ) {
            $this->_redirect('tpay/tpay/Blik');
        } else {
            $this->redirectToPayment($orderId, $additionalPaymentInformation);

            $session->unsQuoteId();
        }
    }

    /** Redirect to tpay.com
     * @param $orderId
     * @param $additionalPaymentInformation
     */

    private function redirectToPayment($orderId, $additionalPaymentInformation)
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'tpaycom\tpay\Block\Payment\tpay\Redirect')->getFormHtml($orderId, $additionalPaymentInformation)
        );
    }
}
