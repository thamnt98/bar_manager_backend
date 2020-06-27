<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Bottle;


interface BottleRepository
{
    /**
     * find bottle by list barId
     * @param $barIds
     * @return mixed
     */
    public function findBottleByListBarId($barIds, $sort);

    /**
     * return if name is duplicated
     * @param $name
     * @return mixed
     */
    public function isNameDuplicated($name, $id, $categoryId);

    /**
     * return if serial is duplicated
     * @param $serial
     * @return mixed
     */
    public function isSerialDuplicated($serial, $id, $categoryId);

    /**
     * delete list bottle
     * @param $bottleId
     * @return mixed
     */
    public function deleteListBottle($bottleId);

    /**
     * find bottle by barId
     * @param $barId
     * @return mixed
     */
    public function findBottleByBarId($barId);

    /**
     * modify list bottle
     * @param $inputBottleList
     * @return mixed
     */
    public function modifyBottleList($inputBottleList);

    public function getAllBottles();
    public function sortBottleByMultiField($barIds, $sort);
    public function getSerialForDuplicateByCategory($categoryId);
    public function getNameForDuplicateByCategory($categoryId);
    public function findBottlesBarsOwnerByBarId($barId);
}
