<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 01/02/17 8:40 PM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Service;

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order;
use tpaycom\tpay\Api\Sales\OrderRepositoryInterface;
use tpaycom\tpay\lib\ResponseFields;

/**
 * Class TpayService
 *
 * @package tpaycom\tpay\Service
 */
class TpayService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Tpay constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Change order state and notify user if needed
     *
     * @param int  $orderId
     * @param bool $sendEmail
     *
     * @return Order
     */
    public function setOrderStatePendingPayment($orderId, $sendEmail)
    {
        /** @var Order $order */
        $order = $this->orderRepository->getByIncrementId($orderId);

        $order->addStatusToHistory(
            Order::STATE_PENDING_PAYMENT,
            __('Waiting for tpay.com payment.')
        );

        $order->setSendEmail($sendEmail);
        $order->save();

        return $order;
    }

    /**
     * Return payment data
     *
     * @param int $orderId
     *
     * @return array
     */
    public function getPaymentData($orderId)
    {
        /** @var Order $order */
        $order = $this->orderRepository->getByIncrementId($orderId);

        return $order->getPayment()->getData();
    }

    /**
     * Validate order and set appropriate state
     *
     * @param int   $orderId
     * @param array $validParams
     *
     * @return bool|Order
     */
    public function validateOrderAndSetStatus($orderId, array $validParams)
    {
        /** @var Order $order */
        $order = $this->orderRepository->getByIncrementId($orderId);

        if (!$order->getId()) {
            return false;
        }

        $payment = $order->getPayment();

        if ($payment) {
            $payment->setData('transaction_id', $validParams[ResponseFields::TR_ID]);
            $payment->addTransaction(Transaction::TYPE_ORDER);
        }

        $orderAmount     = (float)number_format($order->getGrandTotal(), 2);
        $transactionDesc = $this->getTransactionDesc($validParams);
        $trStatus        = $validParams[ResponseFields::TR_STATUS];
        $emailNotify     = false;

        if ($trStatus === 'TRUE' && ($validParams[ResponseFields::TR_AMOUNT] === $orderAmount)) {
            if ($order->getState() != Order::STATE_PROCESSING) {
                $emailNotify = true;
            }

            $order->addStatusToHistory(
                Order::STATE_PROCESSING,
                __('The payment from tpay.com has been accepted.').'</br>'.$transactionDesc,
                true
            );

            $order->setState(Order::STATE_PROCESSING);
        } else {
            if ($order->getState() != Order::STATE_HOLDED) {
                $emailNotify = true;
            }

            $order->addStatusToHistory(
                Order::STATE_HOLDED,
                __('Payment has been canceled: ').'</br>'.$transactionDesc,
                true
            );

            $order->setState(Order::STATE_HOLDED);
        }

        if ($emailNotify) {
            $order->setSendEmail(true);
        }

        $order->save();

        return $order;
    }

    /**
     * Get description for transaction
     *
     * @param array $validParams
     *
     * @return bool|string
     */
    protected function getTransactionDesc(array $validParams)
    {
        if ($validParams === false) {
            return false;
        }

        $error           = $validParams[ResponseFields::TR_ERROR];
        $paid            = $validParams[ResponseFields::TR_PAID];
        $transactionDesc = '<b>'.$validParams[ResponseFields::TR_ID].'</b> ';
        $transactionDesc .= $error === 'none' ? ' ' : ' Error:  <b>'.strtoupper($error).'</b> ('.$paid.')';

        return $transactionDesc.$validParams[ResponseFields::TEST_MODE] === '1' ? '<b> TEST </b>' : ' ';
    }
}