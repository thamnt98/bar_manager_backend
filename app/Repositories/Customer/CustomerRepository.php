<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Customer;


interface CustomerRepository
{

    /**
     * find customer by list barId
     * @param $barIds
     * @return mixed
     * @author HoangNN
     */
    public function findCustomerDataByListBarId($barIds,
                                            $keepBottleInfos,
                                            $search,
                                            $bar,
                                            $favoriteRank,
                                            $incomeRank,
                                            $mustGreaterDateOfBirth,
                                            $mustLessDateOfBirth,
                                            $orderFieldName,
                                            $orderBy,
                                            $sort,
                                            $removeFlag);

    /**
     * find customer by list barId
     * @param $barIds
     * @return mixed
     */
    public function findCustomerByListBarId($barIds,
                                            $keepBottleInfos,
                                            $search,
                                            $bar,
                                            $favoriteRank,
                                            $incomeRank,
                                            $mustGreaterDateOfBirth,
                                            $mustLessDateOfBirth,
                                            $orderFieldName,
                                            $orderBy,
                                            $sort);

    /**
     * find orders by list barId
     * @param $barIds
     * @return mixed
     */
    public function findKeepBottlesByListBarId($barIds, $keepDay, $bottleId, $limit);

    /**
     * find customer by id and barId
     * @param $barId
     * @return mixed
     */
    public function findCustomerByIdAndBarId($customerId, $barId,  $keepBottleInfos);

    /**
     * find orders by customerId and barId
     * @param $barId
     * @return mixed
     */
    public function findKeepBottlesByBarId($customerId, $barId);

    /**
     * @param $barIds
     * @param $name
     * @return mixed
     */
    public function findCustomerByBarIds($barIds, $name);
    public function findKeepBottleByCustomer($customerId);
    public function findKeepBottleCanEditByBarId($barIds,$customerId);
    public function modifyKeepBottleList($inputKeepBottleList,$customerId);


    /**
     * remove or restore customers:
     * update is_trash to all customers in ids list
     * @param ids array
     * @param is_trash: 1-remove, 0-not remove
     * @author HoangNN
     */
    public function updateCustomerData($ids, $isTrash);

    /**
     * Get list of customer by bar has birthday by time
     * @param $time
     * @author ThamNT
     */
    public function statisticBirthdayCustomerByBarAndMonth($barId, $month);

    /**
     * Get list of customer by bar has birthday is today
     * @param $now
     * @author ThamNT
     */
    public function statisticBirthdayCustomerByBarNow($barId);

    /**
     * Find bottle name and count customer keep bottle by bar
     * @param $barId, $month
     * @author ThamNT
     */
    public function findKeepBottleByBarIdAndTime($barId, $month, $limit);

    /**
     * Find customer and revenue of customer by bar
     * @param $barId, $time
     * @author ThamNT
     */
    public function getRevenueCustomerByBarId($barId, $time, $limit);

    /**
     * Find customer and count visit of customer by bar
     * @param $barId, $time
     * @author ThamNT
     */
    public function getVisitCountByBarId($barId, $time, $limit);

    /**
     * Find customer and count type honshimei of customer by bar
     * @param $barId, $time
     * @author ThamNT
     */
    public function getShimeiCountByBarId($barId, $time, $limit);
}
