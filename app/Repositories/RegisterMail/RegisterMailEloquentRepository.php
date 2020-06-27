<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/27/19
 * Time: 6:05 PM
 */

namespace App\Repositories\RegisterMail;

use App\Repositories\EloquentRepository;
use Illuminate\Support\Str;

class RegisterMailEloquentRepository extends EloquentRepository implements RegisterMailRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\RegisterMail::class;
    }

    /**
     * find register mail
     * @param $email
     * @param $code
     * @return mixed
     */
    public function findByEmailAndGeneratedCode($email, $code)
    {
        return $this->_model::where('email', $email)->where('generated_code', $code)->first();
    }

    /**
     * @param $email
     * @return mixed
     */
    public function createOrUpdate($email)
    {
        return $this->_model->updateOrCreate(
            ['email' => $email],
            [
                'email' => $email,
                'generated_code' => Str::random(60)
            ]
        );
    }

    /**
     * @param $code
     * @return mixed
     */
    public function findByGeneratedCode($code)
    {
        return $this->_model::where('generated_code', $code)->first();
    }
}