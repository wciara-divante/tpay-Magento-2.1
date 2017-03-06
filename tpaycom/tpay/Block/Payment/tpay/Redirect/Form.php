<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 06/02/17 11:38 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Block\Payment\tpay\Redirect;

use Magento\Framework\View\Element\Template;

/**
 * Class Form
 *
 * @package tpaycom\tpay\Block\Payment\tpay\Redirect
 */
class Form extends Template
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $tpayData = [];

    /**
     * @var array
     */
    protected $additionalInformation = [];

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('tpaycom_tpay::redirect/form.phtml');

        parent::_construct();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
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
     * @param string $action
     *
     * @return Form
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param array $tpayData
     *
     * @return Form
     */
    public function setTpayData(array $tpayData)
    {
        $this->tpayData = $tpayData;

        return $this;
    }

    /**
     * @return array
     */
    public function getTpayData()
    {
        return $this->tpayData;
    }

    /**
     * @param array $additionalInformation
     *
     * @return Form
     */
    public function setAdditionalInformation(array $additionalInformation)
    {
        $this->additionalInformation = array_filter($additionalInformation);

        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }
}
