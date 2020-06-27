<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Cast;


use App\Repositories\EloquentRepository;

class CastEloquentRepository extends EloquentRepository implements CastRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\Cast::class;
    }

    function getListCastByListBarId($barIds)
    {
        return $this->_model::whereIn('bar_id', $barIds)->get();
    }
}
