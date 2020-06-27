<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/27/19
 * Time: 6:05 PM
 */

namespace App\Repositories\RegisterMail;

interface RegisterMailRepository
{
    /**
     * find register mail
     * @param $email
     * @param $code
     * @return mixed
     */
    public function findByEmailAndGeneratedCode($email, $code);

    /**
     * @param $email
     * @return mixed
     */
    public function createOrUpdate($email);

    /**
     * @param $code
     * @return mixed
     */
    public function findByGeneratedCode($code);
}