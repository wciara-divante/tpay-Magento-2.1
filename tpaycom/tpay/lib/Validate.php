<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\lib;

/**
 * Class Validate
 *
 * Include methods responsible for receiving and validating input data
 *
 * @package tpaycom\tpay\lib
 */
class Validate
{
    /**
     * Check one field form
     *
     * @param string $name  field name
     * @param mixed  $value field value
     *
     * @return bool
     *
     * @throws TException
     */
    public static function validateOne($name, $value)
    {
        $requestFields = ResponseFieldsSettings::$fields;

        if (!is_string($name)) {
            throw new TException('Invalid field name');
        }
        if (!array_key_exists($name, $requestFields)) {
            throw new TException('Field with this name is not supported');
        }

        $fieldConfig = $requestFields[$name];

        if ($fieldConfig[FieldProperties::REQ] === false && ($value === '' || $value === false)) {
            return true;
        }

        if (isset($fieldConfig[FieldProperties::VALIDATION]) === true) {
            static::fieldValidation($value, $name);
        }

        return true;
    }

    /**
     * Check that the field is correct
     *
     * @param mixed  $value field value
     * @param string $name  field name
     *
     * @throws TException
     */
    public static function fieldValidation($value, $name)
    {
        $fieldConfig = ResponseFieldsSettings::$fields[$name];
        foreach ($fieldConfig[FieldProperties::VALIDATION] as $validator) {
            switch ($validator) {
                case 'uint':
                    self::validateUint($value, $name);
                    break;
                case Type::FLOAT:
                    self::validateFloat($value, $name);
                    break;
                case Type::STRING:
                    self::validateString($value, $name);
                    break;
                case 'email_list':
                    self::validateEmailList($value, $name);
                    break;
                case FieldProperties::OPTIONS:
                    self::validateOptions($value, $fieldConfig[FieldProperties::OPTIONS], $name);
                    break;
                default:
            }
            static::fieldLengthValidation($validator, $value, $name);
        }
    }

    /**
     * Check length of field
     *
     * @param string $validator requeries for field
     * @param mixed  $value     field value
     * @param string $name      field name
     *
     * @throws TException
     */
    public static function fieldLengthValidation($validator, $value, $name)
    {
        if (strpos($validator, 'maxlenght') === 0) {
            $max = explode('_', $validator);
            $max = (int)$max[1];
            self::validateMaxLenght($value, $max, $name);
        }
        if (strpos($validator, 'minlength') === 0) {
            $min = explode('_', $validator);
            $min = (int)$min[1];
            self::validateMinLength($value, $min, $name);
        }
    }

    /**
     * Check all variables required in response
     * Parse variables to valid types
     *
     * @param null $params array
     *
     * @return array
     * @throws TException
     */
    public static function getResponse($params = null)
    {
        $ready          = [];
        $missed         = [];
        $responseFields = ResponseFieldsSettings::$fields;

        foreach ($responseFields as $fieldName => $field) {
            if (Util::post($fieldName, Type::STRING, $params) === false) {
                if ($field[FieldProperties::REQ] === true) {
                    $missed[] = $fieldName;
                }
            } else {
                $val = Util::post($fieldName, Type::STRING);
                $ready[$fieldName] = static::getFieldValue($field, $val);
            }
        }

        if (count($missed) > 0) {
            throw new TException(sprintf('Missing fields in transferuj response: %s', implode(',', $missed)));
        }

        foreach ($ready as $fieldName => $fieldVal) {
            static::validateOne($fieldName, $fieldVal);
        }

        return $ready;
    }

    /**
     * Check if variable is uint
     *
     * @param mixed  $value variable to check
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateUint($value, $name)
    {
        if (!is_int($value)) {
            throw new TException(sprintf('Field "%s" must be an integer', $name));
        } else {
            if ($value < 0) {
                throw new TException(sprintf('Field "%s" must be higher than zero', $name));
            }
        }
    }

    /**
     * Check if variable is float
     *
     * @param mixed  $value variable to check
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateFloat($value, $name)
    {
        if (!is_float($value) && !is_int($value)) {
            throw new TException(sprintf('Field "%s" must be a float|int number', $name));
        } else {
            if ($value < 0) {
                throw new TException(sprintf('Field "%s" must be higher than zero', $name));
            }
        }
    }

    /**
     * Check if variable is string
     *
     * @param mixed  $value variable to check
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateString($value, $name)
    {
        if (!is_string($value)) {
            throw new TException(sprintf('Field "%s" must be a string', $name));
        }
    }

    /**
     * Check if variable is valid email list
     *
     * @param mixed  $value variable to check
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateEmailList($value, $name)
    {
        if (!is_string($value)) {
            throw new TException(sprintf('Field "%s" must be a string', $name));
        }
        $emails = explode(',', $value);
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new TException(
                    sprintf('Field "%s" contains invalid email address', $name)
                );
            }
        }
    }

    /**
     * Check if variable has expected value
     *
     * @param mixed  $value   variable to check
     * @param array  $options available options
     * @param string $name    field name
     *
     * @throws TException
     */
    private static function validateOptions($value, $options, $name)
    {
        if (!in_array($value, $options, true)) {
            throw new TException(sprintf('Field "%s" has unsupported value', $name));
        }
    }

    /**
     * Check variable max lenght
     *
     * @param mixed  $value variable to check
     * @param int    $max   max lenght
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateMaxLenght($value, $max, $name)
    {
        if (strlen($value) > $max) {
            throw new TException(
                sprintf('Value of field "%s" is too long. Max %d characters', $name, $max)
            );
        }
    }

    /**
     * Check variable min length
     *
     * @param mixed  $value variable to check
     * @param int    $min   min length
     * @param string $name  field name
     *
     * @throws TException
     */
    private static function validateMinLength($value, $min, $name)
    {
        if (strlen($value) < $min) {
            throw new TException(
                sprintf('Value of field "%s" is too short. Min %d characters', $name, $min)
            );
        }
    }

    /**
     * Validate merchant Id
     *
     * @param int $merchantId
     *
     * @throws TException
     */
    public static function validateMerchantId($merchantId)
    {
        if (!is_int($merchantId) || $merchantId <= 0) {
            throw new TException('Invalid merchantId');
        }
    }

    /**
     * Validate merchant secret
     *
     * @param string $merchantSecret
     *
     * @throws TException
     */
    public static function validateMerchantSecret($merchantSecret)
    {
        if (!is_string($merchantSecret) || strlen($merchantSecret) === 0) {
            throw new TException('Invalid secret code');
        }
    }

    /**
     * Return field value
     *
     * @param $field array
     * @param $val mixed
     * @return mixed
     * @throws TException
     */
    private static function getFieldValue($field, $val)
    {
        switch ($field[FieldProperties::TYPE]) {
            case Type::STRING:
                $val = (string)$val;
                break;
            case Type::INT:
                $val = (int)$val;
                break;
            case Type::FLOAT:
                $val = (float)$val;
                break;
            default:
                throw new TException(sprintf('unknown field type in getResponse - field name= %s', $fieldName));
        }

        return $val;
    }
}
