<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Cast;


interface CastRepository
{
    function getListCastByListBarId($barIds);
}
