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

/**
 * Class Success
 *
 * @package tpaycom\tpay\Controller\tpay
 */
class Success extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->messageManager->addSuccessMessage(__('Dziękujemy za dokonanie płatności.'));

        return $this->_redirect('checkout/cart');
    }
}
