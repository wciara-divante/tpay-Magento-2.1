<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 07/02/17 11:02 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Api\Sales;

use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepositoryInterface;

/**
 * Interface OrderRepositoryInterface
 *
 * @package tpaycom\tpay\Api\Sales
 */
interface OrderRepositoryInterface extends MagentoOrderRepositoryInterface
{
    /**
     * Return new instance of Order by increment ID
     *
     * @param string $incrementId
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getByIncrementId($incrementId);
}
