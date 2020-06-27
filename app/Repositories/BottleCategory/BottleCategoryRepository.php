<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\BottleCategory;


interface BottleCategoryRepository
{
    /**
     * find bottle category by list barId
     * @param $barIds
     * @return mixed
     */
    public function findBottleCategoryByListBarId($barIds, $sort);

    /**
     * return if name is duplicated
     * @param $name
     * @return mixed
     */
    public function isNameDuplicated($name, $id, $barId);

    /**
     * return if serial is duplicated
     * @param $serial
     * @return mixed
     */
    public function isSerialDuplicated($serial, $id, $barId);

    /**
     * delete list bottle category
     * @param $bottleCategoryId
     * @return mixed
     */
    public function deleteListBottleCategroy($bottleCategoryId);

    /**
     * find bottle category by barId
     * @param $barId
     * @return mixed
     */
    public function findBottleCategoryByBarId($barId);

    /**
     * modify list bottle category
     * @param $inputBottleCategoryList
     * @return mixed
     */
    public function modifyBottleCategoryList($inputBottleCategoryList);

    /**
     * list bottle with bar_id
     * @param $barId
     * @return mixed
     */
    public function findByBarId($barId);
    public function getAllBottleCategory();
    public function sortBottleCategoryMultiField ($barIds, $sort);
    public function getSerialForDuplicateByBar($barId);
    public function getNameForDuplicateByBaR($barId);
}
