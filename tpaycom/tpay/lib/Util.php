<?php

/**
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\lib;

/**
 * Class Util
 *
 * Utility class which helps with:
 *  - parsing template files
 *  - class loading
 *  - log library operations
 *  - handle POST array
 *
 * @package tpaycom\tpay\lib
 */
class Util
{
    /**
     * Get value from  array.
     * If not exists return false
     *
     * @param string $name
     * @param string $type   variable type
     *
     * @param null   $params array
     *
     * @return mixed
     * @throws TException
     */
    public static function post($name, $type, $params = null)
    {
        if ($params === null) {
            $params = $_POST;
        }
        if (!isset($params[$name])) {
            return false;
        }
        $val = $params[$name];

        if ($type === 'int') {
            $val = (int)$val;
        } elseif ($type === 'float') {
            $val = (float)$val;
        } elseif ($type === 'string') {
            $val = (string)$val;
        } else {
            throw new TException('Undefined $_POST variable type');
        }

        return $val;
    }

    /**
     * Get substring by pattern
     *
     * @param string $pattern pattern
     * @param string $string  content
     *
     * @return string
     */
    public static function findSubstring($pattern, $string)
    {
        preg_match_all($pattern, $string, $matches);
        if (isset($matches[1]) && isset($matches[1][0])) {
            return $matches[1][0];
        }

        return '';
    }
}
