<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:25 PM
 */

namespace App\Repositories\Job;


use App\Repositories\EloquentRepository;

class JobEloquentRepository extends EloquentRepository implements JobRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\Job::class;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return $this->_model::where('name', $name)->first();
    }
}