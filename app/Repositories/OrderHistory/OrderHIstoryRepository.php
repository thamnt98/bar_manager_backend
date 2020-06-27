<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\OrderHistory;


interface OrderHistoryRepository
{
     /**
     * Find order history
     * Sort order history by:
     * @param barIds: array of bar's id
     * @param month: month
     * @param greaterRemainDebt: remaining debt (to)
     * @param lessRemainDebt: remaing debt (from)
     * @param payPeriod: paymend deadline (out_date : out of date , less_than_month : less than thirty days, more_than_month : more than thirty days)
     * @param cast: array of cast's id
     * @param staff: array of staff's id
     * 
     * @author ThamNT
     */
    public function findOrderHistoryByListBarIds($barIds, $month, $greaterRemainDebt, $lessRemainDebt, $payPeriod, $castIds, $staffIds);

    public function findOrderHistoryByBarIdAndId($barId, $orderId);

    public function findOrderHistoryByCustomerIdAndBarId($customerId, $barId, $month);

    public function findOrderHistoryByCustomerId($customerId, $month);

    public function reportRevenueOrderHistoryByCustomerId($customerId, $month);

    public function reportChartColumnOrderHistoryByCustomerId($customerId, $month);

    public function reportChartDoughnutOrderHistoryByCustomerId($customerId, $month);

    public function reportRevenueOrderHistoryByBarId($barId, $month);

    public function reportChartColumnOrderHistoryByBarId($barId, $month);

    public function reportChartDoughnutOrderHistoryByBarId($barId, $month);
}
