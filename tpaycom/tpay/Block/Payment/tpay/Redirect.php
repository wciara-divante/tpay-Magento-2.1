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

namespace tpaycom\tpay\Block\Payment\tpay;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use tpaycom\tpay\Api\TpayInterface;
use tpaycom\tpay\Block\Payment\tpay\Redirect\Form;

/**
 * Class Redirect
 *
 * @package tpaycom\tpay\Block\Payment\tpay
 */
class Redirect extends Template
{
    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var array
     */
    protected $additionalPaymentInformation = [];

    /**
     * @var TpayInterface
     */
    protected $tpay;

    /**
     * {@inheritdoc}
     *
     * @param TpayInterface $tpayModel
     * @param array         $data
     */
    public function __construct(
        Context $context,
        TpayInterface $tpayModel,
        array $data = []
    ) {
        $this->tpay        = $tpayModel;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('tpaycom_tpay::redirect.phtml');

        parent::_construct();
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @param array $additionalPaymentInformation
     *
     * @return $this
     */
    public function setAdditionalPaymentInformation(array $additionalPaymentInformation)
    {
        $this->additionalPaymentInformation = $additionalPaymentInformation;

        return $this;
    }

    /**
     * Get form Html
     *
     * @return string
     */
    public function getFormHtml()
    {
        /** @var Form $formBlock */
        $formBlock = $this->getChildBlock('form');

        $formBlock
            ->setOrderId($this->orderId)
            ->setAction($this->tpay->getRedirectURL())
            ->setTpayData($this->tpay->getTpayFormData($this->orderId))
            ->setAdditionalInformation($this->additionalPaymentInformation);

        return $formBlock->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->orderId === null) {
            return false;
        }

        $this->addChild('form', 'tpaycom\tpay\Block\Payment\tpay\Redirect\Form');

        return parent::_toHtml();
    }
}
