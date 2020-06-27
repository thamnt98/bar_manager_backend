<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 1/9/20
 * Time: 5:36 PM
 */

namespace App\Repositories\PasswordReset;


use App\Repositories\EloquentRepository;
use Illuminate\Support\Str;

class PasswordResetEloquentRepository extends EloquentRepository implements PasswordResetRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\PasswordReset::class;
    }

    public function createOrUpdate($email)
    {
        return $this->_model->updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Str::random(60)
            ]
        );
    }

    /**
     * @param $token
     * @return mixed
     */
    public function findByToken($token)
    {
        return $this->_model->where('token', $token)->first();
    }

    /**
     * @param $email
     * @param $token
     * @return mixed
     */
    public function findByEmailAndToken($email, $token)
    {
        return $this->_model->where([['token', $token], ['email', $email]])->first();
    }
}