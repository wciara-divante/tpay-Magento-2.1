<?php
/*
* This file is part of the "TPay" package.
*
* (c) Divante Sp. z o. o.
*
* Author: Oleksandr Yeremenko <oyeremenko@divante.pl>
* Date: 07/02/17 11:06 AM
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace tpaycom\tpay\Model\Sales;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use tpaycom\tpay\Api\Sales\OrderRepositoryInterface;

/**
 * Class OrderRepository
 *
 * @package tpaycom\tpay\Model\Sales
 */
class OrderRepository extends MagentoOrderRepository implements OrderRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getByIncrementId($incrementId)
    {
        if (!$incrementId) {
            throw new InputException(__('Id required'));
        }

        /** @var OrderInterface $entity */
        $entity = $this->metadata->getNewInstance()->loadByIncrementId($incrementId);

        if (!$entity->getEntityId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        return $entity;
    }
}
