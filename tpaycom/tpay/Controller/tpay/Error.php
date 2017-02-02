<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Controller\tpay;

/**
 * Class Error
 * @package tpaycom\tpay\Controller\tpay
 */

class Error extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $this->messageManager->addWarningMessage(__("Wystąpił błąd podczas płatności."));
        $this->_redirect('checkout/cart');
    }
}
