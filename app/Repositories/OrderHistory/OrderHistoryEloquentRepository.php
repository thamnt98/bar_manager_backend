<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\OrderHistory;


use App\Models\OrderHistory;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class OrderHistoryEloquentRepository extends EloquentRepository implements OrderHistoryRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\OrderHistory::class;
    }

    public function findOrderHistoryByListBarIds($barIds, $month, $lessRemainDebt, $greaterRemainDebt, $payPeriod, $castIds, $staffIds)
    {
        $query = DB::table('order_histories')->whereIn('order_histories.bar_id', $barIds)
            ->select(DB::raw('order_histories.*,
                            customers.name as customer_name,
                            customers.id as customer_id,
                            customers.icon as customer_icon,
                            customers.furigana_name as customer_furigana_name,
                            bars.name as bar_name,
                            bars.id as bar_id,
                            casts.name as in_charge_cast,
                            IFNULL(paidMoney.remain_debt, order_histories.debt) as remain_debt,
                            IFNULL(paidMoney.paid_money, 0) as paid_money'))
            ->whereNull('order_histories.deleted_at')
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->leftJoin('bars', 'bars.id', '=', 'order_histories.bar_id')
            ->leftJoin('casts', 'casts.id', '=', 'order_histories.cast_id')
            ->leftJoin(DB::raw('(SELECT SUM(debit_histories.paid_money) as paid_money, debit_histories.order_id, order_histories.debt - SUM(debit_histories.paid_money) as remain_debt FROM debit_histories, order_histories WHERE debit_histories.deleted_at IS NULL AND debit_histories.order_id = order_histories.id GROUP BY order_histories.id) AS paidMoney'), function ($query) {
                $query->on('paidMoney.order_id', '=', 'order_histories.id');
            })
            ->where('customers.is_trash', '=', false);
        $query = $this->appendQueryMonth($query, $month);

        if (!is_null($lessRemainDebt)) {
            $query->where(DB::raw('IFNULL(paidMoney.remain_debt,order_histories.debt)'), '>=', $lessRemainDebt);
        };
        if (!is_null($greaterRemainDebt)) {
            $query->where(DB::raw('IFNULL(paidMoney.remain_debt,order_histories.debt)'), '<=', $greaterRemainDebt);
        };
        if (!is_null($payPeriod)) {
            switch ($payPeriod) {
                case 'out_date':
                    $query->where('order_histories.pay_day', '<', date('Y-m-d'));
                    break;
                case 'more_than_month':
                    $query->where('order_histories.pay_day', '>=', date('Y-m-d', strtotime('+1 months')));
                    break;
                case 'less_than_month':
                    $query->where('order_histories.pay_day', '>=', date('Y-m-d'))
                        ->where('order_histories.pay_day', '<=', date('Y-m-d', strtotime('+1 months')));
                    break;
                default:
                    break;
            }
        };
        if (!is_null($castIds)) {
            $castIds = array_map('intval', explode(',', $castIds));
            $query->whereIn('order_histories.cast_id', $castIds);
        };
        if (!is_null($staffIds)) {
            $staffIds = array_map('intval', explode(',', $staffIds));
            $query->whereIn('order_histories.staff_id', $staffIds);
        };
        return $query
            ->orderBy('order_histories.arrival_at', 'DESC')->get();
    }

    public function findOrderHistoryByBarIdAndId($barId, $orderId)
    {
        return DB::table('order_histories')->where('order_histories.bar_id', $barId)
            ->whereNull('order_histories.deleted_at')
            ->where('order_histories.id', $orderId)
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->leftJoin('bars', 'bars.id', '=', 'order_histories.bar_id')
            ->leftJoin('casts', 'casts.id', '=', 'order_histories.cast_id')
            ->get(['order_histories.*',
                'customers.name as customer_name',
                'customers.id as customer_id',
                'customers.icon as customer_icon',
                'customers.furigana_name as customer_furigana_name',
                'bars.name as bar_name',
                'bars.id as bar_id',
                'casts.name as in_charge_cast'])
            ->first();
    }

    public function findOrderHistoryByCustomerIdAndBarId($customerId, $barId, $month)
    {
        $query =  DB::table('order_histories')->where('order_histories.bar_id', $barId)
            ->where('order_histories.customer_id', $customerId)
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->leftJoin('bars', 'bars.id', '=', 'order_histories.bar_id')
            ->leftJoin('casts', 'casts.id', '=', 'order_histories.cast_id');
        $query = $this->appendQueryMonth($query, $month);
        return $query
            ->get(['order_histories.*',
                'customers.name as customer_name',
                'customers.id as customer_id',
                'customers.icon as customer_icon',
                'customers.furigana_name as customer_furigana_name',
                'bars.name as bar_name',
                'bars.id as bar_id',
                'casts.name as in_charge_cast']);
    }

    public function findOrderHistoryByCustomerId($customerId, $month)
    {
        $query =  DB::table('order_histories')->where('order_histories.customer_id', $customerId)
            ->whereNull('order_histories.deleted_at')
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->leftJoin('bars', 'bars.id', '=', 'order_histories.bar_id')
            ->leftJoin('casts', 'casts.id', '=', 'order_histories.cast_id');
        $query = $this->appendQueryMonth($query, $month);
        return $query
            ->orderBy('order_histories.arrival_at', 'DESC')
            ->get(['order_histories.*',
                'customers.name as customer_name',
                'customers.id as customer_id',
                'customers.icon as customer_icon',
                'customers.furigana_name as customer_furigana_name',
                'bars.name as bar_name',
                'bars.id as bar_id',
                'casts.name as in_charge_cast']);
    }

    private function appendQueryMonth($query, $month) {
        if (!is_null($month)) {
            $monthAndYear = explode('-', $month);
            return $query->where(DB::raw('YEAR(order_histories.arrival_at)'), '=', $monthAndYear[1])
                ->where(DB::raw('MONTH(order_histories.arrival_at)'), '=', $monthAndYear[0]);
        }
       return $query;
    }

    public function reportRevenueOrderHistoryByCustomerId($customerId, $month)
    {
        if(is_null($month)) {
            $query = 'SELECT IFNULL(SUM(d.total_income),0) as total_income,
                               COUNT(CASE
                                         WHEN d.type = \'honshimei\' THEN 1
                                         ELSE NULL
                                     END) AS shimei_count,
                               COUNT(CASE
                                         WHEN d.type = \'douhan\' THEN 1
                                         ELSE NULL
                                     END) AS douhan_count,
                               COUNT(d.customer_id) AS visit_count
                        FROM
                          (SELECT order_histories.total_income,
                                  order_histories.type,
                                  order_histories.customer_id
                           FROM order_histories
                           LEFT JOIN `customers` ON `customers`.`id` = `order_histories`.`customer_id`
                           LEFT JOIN `bars` ON `bars`.`id` = `customers`.`bar_id`
                           LEFT JOIN `bar_memberships` ON `bar_memberships`.`bar_id` = `bars`.`id`
                           LEFT JOIN `accounts` ON `accounts`.`id` = `bar_memberships`.`account_id`
                           WHERE `order_histories`.`deleted_at` IS NULL
                             AND `order_histories`.`customer_id` = :customerId
                           GROUP BY order_histories.id) AS d';
            return DB::select($query, compact('customerId'));
        } else {
            $dates = OrderHistory::subTimeMonth($month);
            $query = 'SELECT IFNULL(SUM(d.total_income),0) as total_income,
                               COUNT(CASE
                                         WHEN d.type = \'honshimei\' THEN 1
                                         ELSE NULL
                                     END) AS shimei_count,
                               COUNT(CASE
                                         WHEN d.type = \'douhan\' THEN 1
                                         ELSE NULL
                                     END) AS douhan_count,
                               COUNT(d.customer_id) AS visit_count
                        FROM
                          (SELECT order_histories.total_income,
                                  order_histories.type,
                                  order_histories.customer_id
                           FROM order_histories
                           LEFT JOIN `customers` ON `customers`.`id` = `order_histories`.`customer_id`
                           LEFT JOIN `bars` ON `bars`.`id` = `customers`.`bar_id`
                           LEFT JOIN `bar_memberships` ON `bar_memberships`.`bar_id` = `bars`.`id`
                           LEFT JOIN `accounts` ON `accounts`.`id` = `bar_memberships`.`account_id`
                           WHERE `order_histories`.`deleted_at` IS NULL
                             AND `order_histories`.`customer_id` = :customerId
                             AND `order_histories`.`arrival_at` BETWEEN :firstTime AND :endTime
                           GROUP BY order_histories.id) AS d';
            return DB::select($query, array_merge($dates, compact('customerId')));
        }
    }

    public function reportChartColumnOrderHistoryByCustomerId($customerId, $month)
    {
        $dates = OrderHistory::subTimeAgo($month);
        $firstTime = $dates['firstTime'];
        $endTime = $dates['endTime'];

        $query = "SELECT `all_months`.`month`,
       `all_months`.`year`,
       IFNULL(d.total_income, 0) AS total_income,
       IFNULL(d.visit_count, 0) AS visit_count
FROM
  (SELECT month('".$endTime."') MONTH,
                          year('".$endTime."') YEAR,
                                         0 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 1 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 1 MONTH)) YEAR,
                                                                                                           1 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 2 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 2 MONTH)) YEAR,
                                                                                                           2 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 3 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 3 MONTH)) YEAR,
                                                                                                           3 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 4 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 4 MONTH)) YEAR,
                                                                                                           4 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 5 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 5 MONTH)) YEAR,
                                                                                                           5 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 6 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 6 MONTH)) YEAR,
                                                                                                           6 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 7 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 7 MONTH)) YEAR,
                                                                                                           7 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 8 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 8 MONTH)) YEAR,
                                                                                                           8 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 9 MONTH)) MONTH,
                                                              year(date_add('".$endTime."', INTERVAL - 9 MONTH)) YEAR,
                                                                                                           9 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 10 MONTH)) MONTH,
                                                               year(date_add('".$endTime."', INTERVAL - 10 MONTH)) YEAR,
                                                                                                             10 monthOrder
   UNION SELECT month(date_add('".$endTime."', INTERVAL - 11 MONTH)) MONTH,
                                                               year(date_add('".$endTime."', INTERVAL - 11 MONTH)) YEAR,
                                                                                                             11 monthOrder) AS all_months
LEFT JOIN
  (SELECT c.arrival_at AS arrival_at,
          SUM(c.total_income) AS total_income,
          COUNT(c.customer_id) AS visit_count
   FROM
     (SELECT order_histories.arrival_at AS arrival_at,
             (order_histories.total_income) AS total_income,
             order_histories.customer_id
      FROM `order_histories`
      LEFT JOIN `customers` ON `customers`.`id` = `order_histories`.`customer_id`
      LEFT JOIN `bars` ON `bars`.`id` = `customers`.`bar_id`
      LEFT JOIN `bar_memberships` ON `bar_memberships`.`bar_id` = `bars`.`id`
      LEFT JOIN `accounts` ON `accounts`.`id` = `bar_memberships`.`account_id`
      WHERE `order_histories`.`deleted_at` IS NULL
        AND `order_histories`.`customer_id` = $customerId
        AND `order_histories`.`arrival_at` BETWEEN '".$firstTime."' AND '".$endTime."'
      GROUP BY `arrival_at`) AS c GROUP BY MONTH(arrival_at)) AS d ON all_months.month = DATE_FORMAT(d.arrival_at, '%c')
AND all_months.year = DATE_FORMAT(d.arrival_at, '%Y')
ORDER BY all_months.monthOrder DESC";

        return DB::select($query);
    }

    public function reportChartDoughnutOrderHistoryByCustomerId($customerId, $month)
    {
        $query =  $this->reportOrderHistoryByCustomerId($customerId)
            ->select(
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'honshimei\' THEN 1 ELSE NULL END)/COUNT(order_histories.customer_id)*100, 0) as shimei_percent'),
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'douhan\' THEN 1 ELSE NULL END)/COUNT(order_histories.customer_id)*100, 0) as douhan_percent'),
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'free\' THEN 1 ELSE NULL END)/COUNT(order_histories.customer_id)*100, 0) as free_percent')
            );
        $query = $this->appendQueryOneYearAgo($query, $month);
        return $query->get();
    }

    private function reportOrderHistoryByCustomerId($customerId)
    {
        $query =  DB::table('order_histories')->where('order_histories.customer_id', $customerId)
            ->whereNull('order_histories.deleted_at')
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->leftJoin('bars', 'bars.id', '=', 'customers.bar_id')
            ->leftJoin('bar_memberships', 'bar_memberships.bar_id', '=', 'bars.id')
            ->leftJoin('accounts', 'accounts.id', '=', 'bar_memberships.account_id');
        return $query;
    }

    private function reportOrderHistoryByBarId($barId)
    {
        $query =  DB::table('order_histories')->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')->where('order_histories.bar_id', $barId)
            ->where('customers.is_trash', 0)
            ->whereNull('order_histories.deleted_at');
        return $query;
    }

    private function appendQueryOneYearAgo($query, $month) {
        if (!is_null($month)) {
            $dateTime = OrderHistory::subTimeAgo($month);
            return $query->whereBetween('order_histories.arrival_at', $dateTime);
        }
        return $query;
    }

    public function reportRevenueOrderHistoryByBarId($barId, $month)
    {
        if (is_null($month)) {
            $query = 'SELECT IFNULL(SUM(d.total_income),0) as total_income,
                               COUNT(CASE
                                         WHEN d.type = \'honshimei\' THEN 1
                                         ELSE NULL
                                     END) AS shimei_count,
                               COUNT(CASE
                                         WHEN d.type = \'douhan\' THEN 1
                                         ELSE NULL
                                     END) AS douhan_count,
                               COUNT(d.id) AS visit_count
                           FROM 
                           (SELECT `order_histories`.`total_income`,
                                  `order_histories`.`type`,
                                  `order_histories`.`id`
                           FROM `order_histories`
                           LEFT JOIN `customers` ON `customers`.`id` = `order_histories`.`customer_id`
                           WHERE `order_histories`.`deleted_at` IS NULL
                             AND `customers`.`is_trash` = 0
                             AND `order_histories`.`deleted_at` IS NULL 
                             AND `order_histories`.`bar_id` = :barId ) as d';
            return DB::select($query, compact('barId'));
        } else {
            $dates = OrderHistory::subTimeMonth($month);
            $firstTime = $dates['firstTime'];
            $endTime = $dates['endTime'];
            $query = 'SELECT IFNULL(SUM(d.total_income),0) as total_income,
                               COUNT(CASE
                                         WHEN d.type = \'honshimei\' THEN 1
                                         ELSE NULL
                                     END) AS shimei_count,
                               COUNT(CASE
                                         WHEN d.type = \'douhan\' THEN 1
                                         ELSE NULL
                                     END) AS douhan_count,
                               COUNT(d.id) AS visit_count
                            FROM   
                           (SELECT `order_histories`.`total_income`,
                                  `order_histories`.`type`,
                                  `order_histories`.`id`
                           FROM `order_histories`
                           JOIN `customers` ON `customers`.`id` = `order_histories`.`customer_id`
                           WHERE `order_histories`.`deleted_at` IS NULL
                             AND `customers`.`is_trash` = 0
                             AND `order_histories`.`bar_id` = :barId 
                             AND `order_histories`.`arrival_at` BETWEEN :firstTime AND :endTime) as d';
            return DB::select($query, array_merge($dates, compact('barId')));
        }
    }

    public function reportChartColumnOrderHistoryByBarId($barId, $month)
    {

        $dates = OrderHistory::subTimeAgo($month);
        $firstTime = $dates['firstTime'];
        $endTime = $dates['endTime'];

        $query = "SELECT `all_months`.`month`,
       `all_months`.`year`,
       IFNULL(d.total_income, 0) AS total_income,
       IFNULL(d.visit_count, 0) AS visit_count
FROM
  (SELECT month('" . $endTime . "') MONTH,
                          year('" . $endTime . "') YEAR,
                                         0 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 1 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 1 MONTH)) YEAR,
                                                                                                           1 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 2 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 2 MONTH)) YEAR,
                                                                                                           2 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 3 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 3 MONTH)) YEAR,
                                                                                                           3 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 4 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 4 MONTH)) YEAR,
                                                                                                           4 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 5 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 5 MONTH)) YEAR,
                                                                                                           5 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 6 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 6 MONTH)) YEAR,
                                                                                                           6 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 7 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 7 MONTH)) YEAR,
                                                                                                           7 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 8 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 8 MONTH)) YEAR,
                                                                                                           8 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 9 MONTH)) MONTH,
                                                              year(date_add('" . $endTime . "', INTERVAL - 9 MONTH)) YEAR,
                                                                                                           9 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 10 MONTH)) MONTH,
                                                               year(date_add('" . $endTime . "', INTERVAL - 10 MONTH)) YEAR,
                                                                                                             10 monthOrder
   UNION SELECT month(date_add('" . $endTime . "', INTERVAL - 11 MONTH)) MONTH,
                                                               year(date_add('" . $endTime . "', INTERVAL - 11 MONTH)) YEAR,
                                                                                                             11 monthOrder) AS all_months
LEFT JOIN
  (SELECT order_histories.arrival_at AS arrival_at,
          SUM(order_histories.total_income) AS total_income,
       COUNT(order_histories.customer_id) AS visit_count
   FROM `order_histories`, customers
   WHERE `order_histories`.`deleted_at` IS NULL
      AND `order_histories`.`customer_id` = `customers`.`id`
      AND `customers`.`is_trash` = 0
      AND `order_histories`.`bar_id` = $barId
      AND `order_histories`.`arrival_at` BETWEEN '" . $firstTime . "' AND '" . $endTime . "'
    GROUP BY MONTH(arrival_at)) AS d ON all_months.month = DATE_FORMAT(d.arrival_at, '%c')
AND all_months.year = DATE_FORMAT(d.arrival_at, '%Y')
ORDER BY all_months.monthOrder DESC";

        return DB::select($query);
    }

    public function reportChartDoughnutOrderHistoryByBarId($barId, $month)
    {
        $query =  $this->reportOrderHistoryByBarId($barId)
            ->select(
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'honshimei\' THEN 1 ELSE NULL END)/COUNT(order_histories.id)*100, 0) as shimei_percent'),
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'douhan\' THEN 1 ELSE NULL END)/COUNT(order_histories.id)*100, 0) as douhan_percent'),
                DB::raw('IFNULL(COUNT(CASE WHEN order_histories.type = \'free\' THEN 1 ELSE NULL END)/COUNT(order_histories.id)*100, 0) as free_percent')
            );
        $query = $this->appendQueryOneYearAgo($query, $month);
        return $query->get();
    }
}
