<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.10.2016
 * Time: 10:44
 */

namespace tpay\com\Controler\Tpay;

class verify extends \Magento\Sales\Model\Order  {

function gateway_communication() {
    $ip_table=array(
        '195.149.229.109',
        '148.251.96.163',
        '178.32.201.77',
        '46.248.167.59',
        '46.29.19.106'
    );
    if (in_array($_SERVER['REMOTE_ADDR'], $ip_table) && (!empty($_POST))) {
        $this->verify_payment_response();
    }
    exit;
}

/**
 * Verifies that no errors have occured during transaction
 */
function verify_payment_response() {
    $data['order_id'] = base64_decode($_POST['tr_crc']);
    $data['seller_id'] = $_POST['id'];
    $data['transaction_status'] = $_POST['tr_status'];
    $data['transaction_id'] = $_POST['tr_id'];
    $data['total'] = $_POST['tr_amount'];
    $data['error'] = $_POST['tr_error'];
    $data['crc'] = $_POST['tr_crc'];
    $data['checksum'] = $_POST['md5sum'];

    $data['local_checksum'] = md5($data['seller_id'] . $data['transaction_id'] . $data['total'] . $data['crc'] . $this->getConfigData('api_key'));
    $order_id=base64_decode($data['crc']);
    $order=$this->loadByIncrementId($order_id);
    if (strcmp($data['checksum'], $data['local_checksum']) == 0) {
        if (($data['transaction_status'] == 'TRUE')) {
            if ($data['error'] == 'none') {
                // transaction successful


                $order->setState('Paid');
                $order->setStatus('Payment recieved from tpay.com');
                $order->save();
            } else if ($data['error'] == 'overpay') {
                // payment was bigger than required

            }
        } else {
            // transaction failed

        }
    }

    echo 'TRUE'; // data has been received; response for server
}


}
