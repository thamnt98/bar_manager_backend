<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:26 PM
 */

namespace App\Repositories\Job;


interface JobRepository
{
    /**
     * @param $name
     * @return mixed
     */
    public function findByName($name);
}