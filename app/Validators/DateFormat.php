<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 1/7/20
 * Time: 2:58 PM
 */

namespace App\Validators;


class DateFormat
{
    public function multi_format($attribute, $value, $formats)
    {
        foreach($formats as $format) {
            $parsed = date_parse_from_format($format, $value);

            if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                return true;
            }
        }

        return false;
    }
}