<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\lib;


class ResponseFieldsSettings
{

    /**
     * List of fields available in response for basic payment
     * @var array
     */
    public static $fields = array(
        /**
         * The transaction ID assigned by the system Transferuj
         */
        ResponseFields::TR_ID     => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(Type::STRING),
        ),
        /**
         * Date of transaction.
         */
        ResponseFields::TR_DATE   => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(Type::STRING),

        ),
        /**
         * The secondary parameter to the transaction identification.
         */
        ResponseFields::TR_CRC    => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(Type::STRING),
        ),
        /**
         * Transaction amount.
         */
        ResponseFields::TR_AMOUNT => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::FLOAT,
            FieldProperties::VALIDATION => array(Type::FLOAT),
        ),
        /**
         * The amount paid for the transaction.
         * Note: Depending on the settings, the amount paid can be different than transactions
         * eg. When the customer does overpayment.
         */
        ResponseFields::TR_PAID   => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::FLOAT,
            FieldProperties::VALIDATION => array(Type::FLOAT),
        ),
        /**
         * Description of the transaction.
         */
        ResponseFields::TR_DESC   => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(Type::STRING),
        ),
        /**
         * Transaction status: TRUE in the case of the correct result or FALSE in the case of an error.
         * Note: Depending on the settings, the transaction may be correct status,
         * even if the amount paid is different from the amount of the transaction!
         * Eg. If the Seller accepts the overpayment or underpayment threshold is set.
         */
        ResponseFields::TR_STATUS => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(FieldProperties::OPTIONS),
            FieldProperties::OPTIONS    => array(0, 1, true, false, 'TRUE', 'FALSE'),
        ),
        /**
         * Transaction error status.
         * Could have the following values:
         * - none
         * - overpay
         * - surcharge
         */
        ResponseFields::TR_ERROR  => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(FieldProperties::OPTIONS),
            FieldProperties::OPTIONS    => array('none', 'overpay', 'surcharge'),
        ),
        /**
         * Customer email address.
         */
        ResponseFields::TR_EMAIL  => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array('email_list'),
        ),
        /**
         * The checksum verifies the data sent to the payee.
         * It is built according to the following scheme using the MD5 hash function:
         * MD5(id + tr_id + tr_amount + tr_crc + security code)
         */
        ResponseFields::MD5SUM    => array(
            FieldProperties::REQ        => true,
            FieldProperties::TYPE       => Type::STRING,
            FieldProperties::VALIDATION => array(Type::STRING, 'maxlength_32', 'minlength_32'),
        ),
        /**
         * Transaction marker indicates whether the transaction was executed in test mode:
         * 1 – in test mode
         * 0 – in normal mode
         */
        ResponseFields::TEST_MODE => array(
            FieldProperties::REQ        => false,
            FieldProperties::TYPE       => Type::INT,
            FieldProperties::VALIDATION => array(FieldProperties::OPTIONS),
            FieldProperties::OPTIONS    => array(0, 1),
        ),
        /**
         * The parameter is sent only when you use a payment channel or MasterPass or V.me.
         * Could have the following values: „masterpass” or „vme”
         */
        ResponseFields::WALLET    => array(
            FieldProperties::REQ  => false,
            FieldProperties::TYPE => Type::STRING,
        ),
    );


}