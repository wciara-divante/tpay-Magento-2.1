<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Controller\tpay;

class Success extends \Magento\Framework\App\Action\Action
{


    public function execute()
    {
        $this->messageManager->addSuccessMessage(__('Dziękujemy za dokonanie płatności.'));
        $this->_redirect('checkout/cart');
    }
}
