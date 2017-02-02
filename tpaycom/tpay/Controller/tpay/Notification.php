<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */


namespace tpaycom\tpay\Controller\tpay;

use tpaycom\tpay\lib\PaymentBasic;
use tpaycom\tpay\lib\ResponseFields;

/**
 * Class Notification
 * @package tpaycom\tpay\Controller\tpay
 */
class Notification extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \tpaycom\tpay\Model\Tpay
     */
    private $tpay;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    private $validParams = false;

    private $emailNotify = false;


    /**
     * Notification constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \tpaycom\tpay\Model\Tpay $tpayModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \tpaycom\tpay\Model\Tpay $tpayModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->request = $request;
        $this->tpay = $tpayModel;
        $this->remoteAddress = $remoteAddress;
        parent::__construct($context);

    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {

            $id = $this->tpay->getMerchantId();
            $code = $this->tpay->getSecurityCode();
            $paymentBasic = new PaymentBasic($id, $code);
            $params = $this->request->getParams();
            $this->validParams = $paymentBasic->checkPayment($this->remoteAddress->getRemoteAddress(), $params);
            $this->validateOrder();

            return $this->getResponse()
                ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_200)
                ->setContent('TRUE');

        } catch (\Exception $e) {

            return false;
        }
    }

    /** Set order status for notification from tpay.com
     *
     */

    public function validateOrder()
    {
        $orderId = base64_decode($this->validParams[ResponseFields::TR_CRC]);
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);

        if (!$order->getId()) {
            return false;
        }

        $payment = $order->getPayment();

        if ($payment) {
            $payment->setData('transaction_id', $this->validParams[ResponseFields::TR_ID]);
            $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER);
        }

        $this->setOrderStatus($order);
    }

    /**
     * Set order status
     * @param $order
     */
    public function setOrderStatus($order)
    {
        $orderAmount = (float)number_format($order->getGrandTotal(), 2);
        $transactionDesc = $this->getTransactionDesc();
        $trStatus = $this->validParams[ResponseFields::TR_STATUS];

        if ($trStatus === 'TRUE' && ($this->validParams[ResponseFields::TR_AMOUNT] === $orderAmount)) {
            if ($order->getState() != \Magento\Sales\Model\Order::STATE_PROCESSING) {
                $this->emailNotify = true;
            }
            $order->addStatusToHistory(
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                __('The payment from tpay.com has been accepted.') . '</br>' . $transactionDesc,
                true
            );
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
        } else {
            if ($order->getState() != \Magento\Sales\Model\Order::STATE_HOLDED) {
                $this->emailNotify = true;
            }
            $order->addStatusToHistory(
                \Magento\Sales\Model\Order::STATE_HOLDED,
                __('Payment has been canceled: ') . '</br>' . $transactionDesc, true
            );
            $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED, true);
        }

        if ($this->emailNotify) {
            $order->setSendEmail(true);
        }
        $order->save();
    }

    /** Get description for transaction
     * @return bool|string
     */

    public function getTransactionDesc()
    {

        if ($this->validParams === false) {
            return false;
        }
        $error = $this->validParams[ResponseFields::TR_ERROR];
        $paid = $this->validParams[ResponseFields::TR_PAID];
        $transactionDesc = '<b>' . $this->validParams[ResponseFields::TR_ID] . '</b> ';
        $transactionDesc .= $error === 'none' ? ' ' : ' Error:  <b>' . strtoupper($error) . '</b> (' . $paid . ')';

        echo $transactionDesc;
        return $transactionDesc .= $this->validParams[ResponseFields::TEST_MODE] === '1' ? '<b> TEST </b>' : ' ';

    }
}