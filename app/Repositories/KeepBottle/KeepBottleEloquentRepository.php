<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\KeepBottle;


use App\Repositories\EloquentRepository;

class KeepBottleEloquentRepository extends EloquentRepository implements KeepBottleRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\KeepBottle::class;
    }
}
