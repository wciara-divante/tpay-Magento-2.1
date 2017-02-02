<?php

/**
 *
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Controller\tpay;

/**
 * Class Blik
 * @package tpaycom\tpay\Controller\tpay
 */

class Blik extends \Magento\Framework\App\Action\Action
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

    private $transaction;

    private $orderModel;

    private $transactionFactory;

    /**
     * Blik constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \tpaycom\tpay\Model\Tpay $tpayModel
     * @param \Magento\Sales\Model\Order $orderModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \tpaycom\tpay\Model\Tpay $tpayModel,
        \Magento\Sales\Model\Order $orderModel,
        \tpaycom\tpay\Model\TransactionFactory $transactionFactory

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        parent::__construct($context);
        $this->tpay = $tpayModel;
        $this->orderModel = $orderModel;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            $order = $this->orderModel->loadByIncrementId($orderId);
            $paymentData = $order->getPayment()->getData();
            $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, __('Waiting for payment.'));
            $order->setSendEmail(true);
            $order->save();

            $pass = $this->tpay->getApiPassword();

            $key = $this->tpay->getApiKey();

            $this->transaction = $this->transactionFactory->create(['apiPassword' => $pass, 'apiKey' => $key]);

            $additionalPaymentInformation = $paymentData['additional_information'];

            $result = $this->makeBlikPayment($orderId, $additionalPaymentInformation);
            $session->unsQuoteId();

            if (!$result) {
                $this->_redirect('tpay/tpay/Error');
            } else {
                $this->_redirect('tpay/tpay/Success');
            }
        }
    }

    /** Create  BLIK Payment for transaction data
     * @param $orderId
     * @param $additionalPaymentInformation
     * @return bool
     */

    private function makeBlikPayment($orderId, $additionalPaymentInformation)
    {
        $data = $this->tpay->getTpayFormData($orderId);

        $blikCode = $additionalPaymentInformation['blik_code'];

        unset($additionalPaymentInformation['blik_code']);

        $data = array_merge($data, $additionalPaymentInformation);

        $blikTransactionId = $this->transaction->createBlikTransaction($data);

        if (!$blikTransactionId) {
            return false;
        }

        return $this->blikPay($blikTransactionId, $blikCode);
    }

    /**Send BLIK code for transaction id
     * @param $blikTransactionId
     * @param $blikCode
     * @return mixed
     */

    private function blikPay($blikTransactionId, $blikCode)
    {
        return $this->transaction->sendBlikCode($blikTransactionId, $blikCode);
    }
}
