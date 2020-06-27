<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 1:49 PM
 */

namespace App\Repositories\Bar;


interface BarRepository
{
    /**
     * @param $name
     * @return mixed
     */
    public function findByName($name);

    /**
     * find user by bar
     * @param $bar
     * @return mixed
     */
    public function findUserByBar($bar);

    /**
     * find user by bar and user id
     * @param $bar
     * @return mixed
     */
    public function findUserByBarAndUserId($bar, $userId);

    /**
     * @return mixed
     */
    public function findAllBarIds();

    public function findBarsOwnerByBarId($barId);
    public function findAdminBar($userId);
}
