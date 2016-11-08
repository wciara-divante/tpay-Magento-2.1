<?php
/**
 * Payment CC Types Source Model
 *
 * @category    tpay
 * @package     tpay_com
 * @author      Ivan Weiler & Stjepan Udovičić
 * @copyright   tpay (http://tpay.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace tpay\com\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'JCB', 'OT');
    }
}
