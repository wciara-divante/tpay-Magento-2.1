<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 01/02/17 10:25 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Controller\tpay;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use tpaycom\tpay\Api\TpayInterface;
use tpaycom\tpay\lib\PaymentBasicFactory;
use tpaycom\tpay\lib\ResponseFields;
use tpaycom\tpay\Service\TpayService;

/**
 * Class Notification
 *
 * @package tpaycom\tpay\Controller\tpay
 */
class Notification extends Action
{
    /**
     * @var TpayInterface
     */
    protected $tpay;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var bool
     */
    protected $validParams = false;

    /**
     * @var bool
     */
    protected $emailNotify = false;

    /**
     * @var PaymentBasicFactory
     */
    protected $paymentBasicFactory;

    /**
     * @var TpayService
     */
    protected $tpayService;

    /**
     * {@inheritdoc}
     *
     * @param RemoteAddress $remoteAddress
     * @param TpayInterface $tpayModel
     */
    public function __construct(
        Context $context,
        RemoteAddress $remoteAddress,
        TpayInterface $tpayModel,
        PaymentBasicFactory $paymentBasicFactory,
        TpayService $tpayService
    ) {
        $this->tpay                = $tpayModel;
        $this->remoteAddress       = $remoteAddress;
        $this->paymentBasicFactory = $paymentBasicFactory;
        $this->tpayService         = $tpayService;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $id   = $this->tpay->getMerchantId();
            $code = $this->tpay->getSecurityCode();

            $paymentBasic = $this->paymentBasicFactory->create(['merchantId' => $id, 'merchantSecret' => $code]);

            $params      = $this->getRequest()->getParams();
            $validParams = $paymentBasic->checkPayment($this->remoteAddress->getRemoteAddress(), $params);
            $orderId     = base64_decode($validParams[ResponseFields::TR_CRC]);

            $this->tpayService->validateOrderAndSetStatus($orderId, $validParams);

            return
                $this
                    ->getResponse()
                    ->setStatusCode(Http::STATUS_CODE_200)
                    ->setContent('TRUE');

        } catch (\Exception $e) {
            return false;
        }
    }
}
