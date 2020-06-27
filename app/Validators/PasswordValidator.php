<?php

namespace App\Validators;


class PasswordValidator
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function special($attribute, $value, $parameters)
    {
        return preg_match("/^[a-zA-Z0-9]*$/", $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function lowercase($attribute, $value, $parameters)
    {
        return preg_match("/[a-z]/", $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function uppercase($attribute, $value, $parameters)
    {
        return preg_match("/[A-Z]/", $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function numeric($attribute, $value, $parameters)
    {
        return preg_match("/[0-9]/", $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function startWith($attribute, $value, $parameters)
    {
        return preg_match('/^[A-Za-z0-9]/', $value);
    }
}
