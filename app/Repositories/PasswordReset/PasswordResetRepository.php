<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 1/9/20
 * Time: 5:35 PM
 */

namespace App\Repositories\PasswordReset;


interface PasswordResetRepository
{
    /**
     * @param $email
     * @return mixed
     */
    public function createOrUpdate($email);

    /**
     * @param $token
     * @return mixed
     */
    public function findByToken($token);

    /**
     * @param $email
     * @param $token
     * @return mixed
     */
    public function findByEmailAndToken($email, $token);
}