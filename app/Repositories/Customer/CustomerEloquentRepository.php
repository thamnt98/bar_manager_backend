<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Customer;


use App\Enums\OrderName;
use App\Models\Bottle;
use App\Repositories\EloquentRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Response;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Validation\ValidationException;
use App\Models\OrderHistory;
use App\Util\CustomerUtil;

class CustomerEloquentRepository extends EloquentRepository implements CustomerRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\Customer::class;
    }

    /**
     * @inheritDoc
     */
    public function findCustomerByListBarId(
        $barIds,
        $keepBottleInfos,
        $search,
        $bar,
        $favoriteRank,
        $incomeRank,
        $mustGreaterDateOfBirth,
        $mustLessDateOfBirth,
        $orderFieldName,
        $orderBy,
        $sort
    ) {
        $query = $this->_model::whereIn('customers.bar_id', $barIds)
            ->where('customers.is_trash', '=', false)
            ->leftJoin('accounts', 'customers.in_charge_cast_id', '=', 'accounts.id')
            ->leftJoin('bars', 'bars.id', '=', 'customers.bar_id')
            ->leftJoin('customer_settings', 'bars.id', '=', 'customer_settings.bar_id')
            ->leftJoin(DB::raw('(SELECT customer_id, SUM(order_histories.total_income) as customer_total_income, MAX(order_histories.arrival_at) as last_arrival_time FROM order_histories  WHERE order_histories.deleted_at IS NULL GROUP BY customer_id) AS orderHistories'), function ($query) {
                $query->on('orderHistories.customer_id', '=', 'customers.id');
            })
            ->leftJoin(DB::raw('(SELECT keep_bottles.customer_id AS customer_id, bottles.name AS bottle_name FROM keep_bottles,bottle_categories, bottles WHERE  keep_bottles.bottle_id = bottles.id AND bottles.category_id = bottle_categories.id) AS keepBottles'), function ($query) {
                $query->on('keepBottles.customer_id', '=', 'customers.id');
            })
            ->groupBy('customers.id');
        if (!is_null($mustGreaterDateOfBirth)) {
            $mustGreaterDateOfBirth = explode('-', $mustGreaterDateOfBirth);
            $query->where(function (Builder $queryBuilder) use ($mustGreaterDateOfBirth) {
                return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '>', $mustGreaterDateOfBirth[1])
                    ->orWhere(function (Builder $queryBuilder) use ($mustGreaterDateOfBirth) {
                        return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '=', $mustGreaterDateOfBirth[1])
                            ->where(DB::raw('DAY(customers.date_of_birth)'), '>=', $mustGreaterDateOfBirth[0]);
                    });
            });
        }
        if (!is_null($mustLessDateOfBirth)) {
            $mustLessDateOfBirth = explode('-', $mustLessDateOfBirth);
            $query->where(function (Builder $queryBuilder) use ($mustLessDateOfBirth) {
                return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '<', $mustLessDateOfBirth[1])
                    ->orWhere(function (Builder $queryBuilder) use ($mustLessDateOfBirth) {
                        return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '=', $mustLessDateOfBirth[1])
                            ->where(DB::raw('DAY(customers.date_of_birth)'), '<=', $mustLessDateOfBirth[0]);
                    });
            });
        }
        if (!is_null($search)) {
            $columnsCustomer = ['name', 'furigana_name', 'company_tower', 'email', 'phone_number', 'home_town', 'address', 'feature', 'company_name', 'department', 'position', 'job', 'province', 'district', 'friends', 'day_of_week_can_be_contact'];
            $query->where(function (Builder $queryBuilder) use ($search, $columnsCustomer) {
                $queryBuilder->where('bars.name', 'like', '%' . $search . '%')
                    ->orWhere('accounts.name', 'like', '%' . $search . '%')
                    ->orWhere('keepBottles.bottle_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.date_of_birth', '=', date('Y-m-d', strtotime($search)));

                foreach ($columnsCustomer as $columnCustomer) {
                    $queryBuilder->orWhere('customers.' . $columnCustomer, 'like', '%' . $search . '%');
                }
                return $queryBuilder;
            });
        }
        if (!is_null($bar)) {
            $bar = array_map('intval', explode(',', $bar));
            $query->whereIn('customers.bar_id', $bar);
        }
        if (!is_null($favoriteRank)) {
            $query->where('customers.favorite_rank', '=', $favoriteRank);
        }
        if (!is_null($incomeRank)) {
            $query->where('customers.income_rank', '=', $incomeRank);
        }
        if (!is_null($sort)) {
            $sortList = explode(",", $sort);
            foreach ($sortList as $sortItem) {
                $sortInfo = explode("-", $sortItem);
                if ($sortInfo[0] != 'bottle_name') {
                    $query->orderBy($sortInfo[0], $sortInfo[1]);
                }
            }
        }
        
        return $query
            ->get([
                'customers.*', 'accounts.name as in_charge_cast',
                'customer_total_income',
                'last_arrival_time',
                'bars.name as bar_name',
                'customer_settings.keep_bottle_day_limit'
            ])
            ->map(function ($item) use ($keepBottleInfos) {
                $data['customer_id'] = $item->id;
                $data['bottles'] = null;
                $data['bottles'] = [];
                if (array_key_exists($item->id, $keepBottleInfos)) {
                    $data['bottles'] = $keepBottleInfos[$item->id];
                }
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = is_null($item->icon) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $item->icon;
                $data['company_name'] = $item->company_name;
                $data['in_charge_cast'] = $item->in_charge_cast;
                $data['last_arrival_time'] = $item->last_arrival_time;
                $data['last_arrival_day_number'] = (new \DateTime())->diff(new \DateTime($item->last_arrival_time))->days;
                $data['favorite_rank'] = $item->favorite_rank;
                $data['income_rank'] = $item->income_rank;
                $data['bar_name'] = $item->bar_name;
                $data['customer_total_income'] = $item->customer_total_income;
                $data['keep_bottle_day_limit'] = $item->keep_bottle_day_limit;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                return $data;
            });
    }

    /**
     * @inheritDoc
     * @author HoangNN
     */
    public function findCustomerDataByListBarId(
        $barIds,
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
        $removeFlag
    ) {
        $query = $this->_model::whereIn('customers.bar_id', $barIds)
            ->leftJoin('accounts', 'customers.in_charge_cast_id', '=', 'accounts.id')
            ->leftJoin('bars', 'bars.id', '=', 'customers.bar_id')
            ->leftJoin('customer_settings', 'bars.id', '=', 'customer_settings.bar_id')
            ->leftJoin(DB::raw('(SELECT customer_id, SUM(order_histories.total_income) as customer_total_income, MAX(order_histories.arrival_at) as last_arrival_time FROM order_histories WHERE order_histories.deleted_at IS NULL GROUP BY customer_id) AS orderHistories'), function ($query) {
                $query->on('orderHistories.customer_id', '=', 'customers.id');
            })
            ->leftJoin(DB::raw('(SELECT keep_bottles.customer_id AS customer_id, bottles.name AS bottle_name FROM keep_bottles,bottle_categories, bottles WHERE  keep_bottles.bottle_id = bottles.id AND bottles.category_id = bottle_categories.id) AS keepBottles'), function ($query) {
                $query->on('keepBottles.customer_id', '=', 'customers.id');
            })
            ->groupBy('customers.id');
        if (!is_null($mustGreaterDateOfBirth)) {
            $mustGreaterDateOfBirth = explode('-', $mustGreaterDateOfBirth);
            $query->where(function (Builder $queryBuilder) use ($mustGreaterDateOfBirth) {
                return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '>', $mustGreaterDateOfBirth[1])
                    ->orWhere(function (Builder $queryBuilder) use ($mustGreaterDateOfBirth) {
                        return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '=', $mustGreaterDateOfBirth[1])
                            ->where(DB::raw('DAY(customers.date_of_birth)'), '>=', $mustGreaterDateOfBirth[0]);
                    });
            });
        }
        if (!is_null($mustLessDateOfBirth)) {
            $mustLessDateOfBirth = explode('-', $mustLessDateOfBirth);
            $query->where(function (Builder $queryBuilder) use ($mustLessDateOfBirth) {
                return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '<', $mustLessDateOfBirth[1])
                    ->orWhere(function (Builder $queryBuilder) use ($mustLessDateOfBirth) {
                        return $queryBuilder->where(DB::raw('MONTH(customers.date_of_birth)'), '=', $mustLessDateOfBirth[1])
                            ->where(DB::raw('DAY(customers.date_of_birth)'), '<=', $mustLessDateOfBirth[0]);
                    });
            });
        }
        if (!is_null($search)) {
            $columnsCustomer = ['name', 'furigana_name', 'company_tower', 'email', 'phone_number', 'home_town', 'address', 'feature', 'company_name', 'department', 'position', 'job', 'province', 'district', 'friends', 'day_of_week_can_be_contact'];
            $query->where(function (Builder $queryBuilder) use ($search, $columnsCustomer) {
                $queryBuilder->where('bars.name', 'like', '%' . $search . '%')
                    ->orWhere('accounts.name', 'like', '%' . $search . '%')
                    ->orWhere('keepBottles.bottle_name', 'like', '%' . $search . '%')
                    ->orWhere('customers.date_of_birth', '=', date('Y-m-d', strtotime($search)));

                foreach ($columnsCustomer as $columnCustomer) {
                    $queryBuilder->orWhere('customers.' . $columnCustomer, 'like', '%' . $search . '%');
                }
                return $queryBuilder;
            });
        }

        if (!is_null($bar)) {
            $bar = array_map('intval', explode(',', $bar));
            $query->whereIn('customers.bar_id', $bar);
        }
        if (!is_null($favoriteRank)) {
            $query->where('customers.favorite_rank', '=', $favoriteRank);
        }
        if (!is_null($incomeRank)) {
            $query->where('customers.income_rank', '=', $incomeRank);
        }
        if (!empty($removeFlag) && $removeFlag == 1) {
            $query->where('customers.is_trash', '=', true);
        } else if (!empty($removeFlag) && $removeFlag == 2) {
            $query->where('customers.is_trash', '=', false);
        }

        if (is_null($sort)) {
            switch ($orderFieldName) {
                case OrderName::OptionThree:
                    $orderFieldName = 'customer_settings.' . OrderName::OptionThree;
                    break;
                case OrderName::OptionFive:
                    $orderFieldName = 'accounts.id';
                    break;
                case OrderName::OptionSix:
                    $orderFieldName = OrderName::OptionSix;
                    break;
                case OrderName::OptionTwo:
                    $orderFieldName = 'customers.id';
                    break;
                default:
                    $orderFieldName = 'customers.' . strval($orderFieldName);
                    break;
            }
            $query->orderBy($orderFieldName, $orderBy);
        } else {
            $sortList = explode(",", $sort);
            foreach ($sortList as $sortItem) {
                $sortInfo = explode("-", $sortItem);
                if ($sortInfo[0] != 'bottle_name') {
                  
                    $query->orderBy($sortInfo[0], $sortInfo[1]);
                }
            }
        }

        return $query
            ->get([
                'customers.*', 'accounts.name as in_charge_cast',
                'customer_total_income',
                'last_arrival_time',
                'bars.name as bar_name',
                'customer_settings.keep_bottle_day_limit'
            ])
            ->map(function ($item) use ($keepBottleInfos) {
                $data['customer_id'] = $item->id;
                $data['bottles'] = null;
                $data['bottles'] = [];
                if (array_key_exists($item->id, $keepBottleInfos)) {
                    $data['bottles'] = $keepBottleInfos[$item->id];
                }
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = is_null($item->icon) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $item->icon;
                $data['company_name'] = $item->company_name;
                $data['in_charge_cast'] = $item->in_charge_cast;
                $data['last_arrival_time'] = $item->last_arrival_time;
                $data['favorite_rank'] = $item->favorite_rank;
                $data['income_rank'] = $item->income_rank;
                $data['bar_name'] = $item->bar_name;
                $data['customer_total_income'] = $item->customer_total_income;
                $data['keep_bottle_day_limit'] = $item->keep_bottle_day_limit;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                return $data;
            });
    }

    /**
     * @inheritDoc
     */
    public function findKeepBottlesByListBarId($barIds, $keepDay, $bottleId, $limit)
    {
        $keepBottleInfos = DB::table('customers')->whereIn('customers.bar_id', $barIds)
            ->join('keep_bottles', 'keep_bottles.customer_id', '=', 'customers.id')
            ->join('bottles', 'bottles.id', '=', 'keep_bottles.bottle_id')
            ->leftJoin('customer_settings', 'keep_bottles.bar_id', '=', 'customer_settings.bar_id')
            ->where('bottles.is_trash', 0)
            ->where('keep_bottles.is_trash', 0);
        switch ($keepDay) {
            case 'out_date':
                $keepBottleInfos = $keepBottleInfos->where(DB::raw('DATEDIFF(CURRENT_TIMESTAMP,keep_bottles.created_at)'), '>', DB::raw('customer_settings.keep_bottle_day_limit'));
                break;
            case 'more_than_month':
                $keepBottleInfos = $keepBottleInfos->where(DB::raw('DATEDIFF(CURRENT_TIMESTAMP,keep_bottles.created_at)'), '<=', DB::raw('customer_settings.keep_bottle_day_limit - 30'));
                break;
            case 'less_than_month':
                $keepBottleInfos = $keepBottleInfos->where(DB::raw('DATEDIFF(CURRENT_TIMESTAMP,keep_bottles.created_at)'), '<=', DB::raw('customer_settings.keep_bottle_day_limit'))
                    ->where(DB::raw('DATEDIFF(CURRENT_TIMESTAMP,keep_bottles.created_at)'), '>', DB::raw('customer_settings.keep_bottle_day_limit - 30'));
                break;
            default:
                break;
        }
        if (!is_null($bottleId)) {
            $bottleName = DB::table('bottles')->find($bottleId)->name;
            $keepBottleInfos = $keepBottleInfos->where('bottles.name', '=', $bottleName);
        }
        $keepBottleInfos=$keepBottleInfos->groupBy('keep_bottles.id');
        $keepBottleInfos = $keepBottleInfos->get(['customers.id', 'keep_bottles.remain', 'keep_bottles.created_at', 'bottles.name', 'bottles.code', 'bottles.id as bottle_id']);
        $result = [];
        foreach (collect($keepBottleInfos) as $keepBottleInfo) {
            $result[$keepBottleInfo->id][] = $keepBottleInfo;
        }
        return $result;
    }

    /**
     * @inheritDoc
     * HoangNN modify: add where('customers.is_trash', 0)
     */
    public function findCustomerByIdAndBarId($customerId, $barId, $keepBottleInfos)
    {
        return $this->_model::where('customers.bar_id', $barId)
            ->where('customers.id', $customerId)
            ->where('customers.is_trash', 0)
            ->leftJoin('accounts', 'customers.in_charge_cast_id', '=', 'accounts.id')
            ->leftJoin(DB::raw('(SELECT customer_id, SUM(order_histories.total_income) as customer_total_income, MAX(order_histories.arrival_at) as last_arrival_day FROM order_histories WHERE order_histories.deleted_at IS NULL GROUP BY customer_id) AS orderHistories'), function ($query) {
                $query->on('orderHistories.customer_id', '=', 'customers.id');
            })
            ->leftJoin('bars', 'bars.id', '=', 'customers.bar_id')
            ->leftJoin('customer_settings', 'bars.id', '=', 'customer_settings.bar_id')
            ->get(['customers.*', 'accounts.name as in_charge_cast', 'bars.name as bar_name', 'customer_settings.keep_bottle_day_limit', 'last_arrival_day'])
            ->map(function ($item) use ($keepBottleInfos) {
                $data['customer_id'] = $item->id;
                $data['bottles'] = [];
                if (array_key_exists($item->id, $keepBottleInfos)) {
                    $data['bottles'] = $keepBottleInfos[$item->id];
                }
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = is_null($item->icon) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $item->icon;
                $data['company_name'] = $item->company_name;
                $data['in_charge_cast'] = $item->in_charge_cast;
                $data['keep_bottle_day_limit'] = $item->keep_bottle_day_limit;
                $data['favorite_rank'] = $item->favorite_rank;
                $data['bar_name'] = $item->bar_name;
                $data['income_rank'] = $item->income_rank;
                $data['age'] = $item->age;
                $data['date_of_birth'] = $item->date_of_birth;
                $data['email'] = $item->email;
                $data['phone_number'] = $item->phone_number;
                $data['line_account_id'] = $item->line_account_id;
                $data['department'] = $item->department;
                $data['post_no'] = $item->post_no;
                $data['position'] = $item->position;
                $data['job'] = $item->job;
                $data['company_tower'] = $item->company_tower;
                $data['province'] = $item->province;
                $data['district'] = $item->district;
                $data['address'] = $item->address;
                $data['note'] = $item->note;
                $data['day_of_week_can_be_contact'] = $item->day_of_week_can_be_contact;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                $data['bar_id'] = $item->bar_id;
                $data['friends'] = $item->friends;
                $data['last_arrival_time'] = $item->last_arrival_day;
                $data['last_arrival_day_number'] = (new \DateTime())->diff(new \DateTime($item->last_arrival_day))->d;
                return $data;
            });
    }

    /**
     * @inheritDoc
     */
    public function findKeepBottlesByBarId($customerId, $barId)
    {
        $keepBottleInfos = DB::table('customers')
            ->where('customers.bar_id', $barId)
            ->where('customers.id', $customerId)
            ->join('keep_bottles', 'keep_bottles.customer_id', '=', 'customers.id')
            ->join('bottles', 'bottles.id', '=', 'keep_bottles.bottle_id')
            ->where('bottles.is_trash', 0)
            ->get(['customers.id', 'keep_bottles.remain', 'keep_bottles.created_at', 'bottles.name', 'bottles.code', 'bottles.is_trash  as bottle_trash',]);
        $result = [];
        foreach (collect($keepBottleInfos) as $keepBottleInfo) {
            $result[$keepBottleInfo->id][] = $keepBottleInfo;
        }
        return $result;
    }

    /**
     * @param $barIds
     * @param $name
     * @return mixed
     */
    public function findCustomerByBarIds($barIds, $name)
    {
        $query = $this->_model::whereIn('customers.bar_id', $barIds)->where('customers.is_trash', false);
        if (!is_null($name)) {
            $query = $query->where('customers.name', 'like', '%' . $name . '%');
        }
        return $query->orderBy('customers.name', 'desc')->get();
    }

    public function separateCsvToCustomersAndKeepBottles($data, $request)
    {
        $customerData = [];
        $keepBottleData = [];
        DB::beginTransaction();
        foreach ($data as $index => $customer) {
            $customerData[$index] = array(
                'name' => $customer[0],
                'furigana_name' => $customer[1],
                'date_of_birth' => $customer[2],
                'email' => $customer[3],
                'phone_number' => $customer[4],
                'day_of_week_can_be_contact' => $customer[5],
                'company_name' => $customer[10],
                'department' => $customer[11],
                'position' => $customer[12],
                'job' => $customer[13],
                'post_no' => $customer[14],
                'province' => $customer[15],
                'district' => $customer[16],
                'address' => $customer[17],
                'company_tower' => $customer[18],
                'note' => $customer[19],
                'bar_id' => $request->bar_id
            );
            $bottle = Bottle::where('name', trim($customer[7]))->first();
            if (!empty($customer[7]) && is_null($bottle)) {
                throw new NotFoundHttpException(trans('error.bottle.not_found') . trans('validation.specific_in_line') . ($index + 2) . trans('validation.specific_in_column') . "8");
            }
            $cast = User::select('accounts.*')
                ->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id')
                ->where('bar_memberships.role', UserRole::Cast)
                ->where('accounts.name', trim($customer[6]))
                ->first();
            if (!empty($customer[6]) && is_null($cast)) {
                throw new NotFoundHttpException(trans('error.cast.not_found') .  trans('validation.specific_in_line') . ($index + 2) . trans('validation.specific_in_column') . '7');
            }
            $validatorCsv = Validator::make($customerData[$index], [
                'name' => 'bail|required|filled|max:255',
                "furigana_name" => 'bail|required|filled|max:255|regex:/^[ぁ-んァ-ン]+$/',
                "company_name" => 'max:255',
                "date_of_birth" => 'date_multi_format:"Y/m/d","Y-m-d"date_multi_format:"Y/m/d","Y-m-d"',
                "email" => 'email',
                "post_no" => 'regex:/^\d{3}[-]\d{4}/i',
            ]);
            if ($validatorCsv->fails()) {
                throw new ValidationException($validatorCsv->errors()->first() . trans('validation.specific_in_line') . ($index + 2));
            }
            if (!empty($customer[6])) {
                array_merge($customerData[$index], ['in_charge_cast_id' => $cast->id]);
            }
            if (empty($customer[2])) {
                $customerData[$index]['date_of_birth'] = null;
            }
            if (empty($customer[14])) {
                $customerData[$index]['post_no'] = null;
            }
            if (!empty($customer[7])) {
                $keepBottleData[$index] = array(
                    'bottle_id' => $bottle->id,
                    'remain' =>  $customer[8],
                    'created_at' => $customer[9],
                    'price' => 0,
                    'bar_id' => $request->bar_id
                );
                $validator = Validator::make($keepBottleData[$index], [
                    "remain" => 'bail|required|numeric',
                    "created_at" => 'bail|required|date_multi_format:"Y/m/d","Y-m-d"date_multi_format:"Y/m/d","Y-m-d"',
                ]);
                if ($validator->fails()) {
                    throw new ValidationException($validator->errors()->first() . trans('validation.specific_in_line')  . ($index + 2));
                }
            }
        };
        DB::commit();
        return ['customers' => $customerData, 'keepBottles' => $keepBottleData];
    }
    public function findKeepBottleByCustomer($customerId)
    {
        $keepBottleInfos = DB::table('customers')
            ->where('customers.id', $customerId)
            ->leftJoin('keep_bottles', 'keep_bottles.customer_id', '=', 'customers.id')
            ->leftJoin('bottles', 'bottles.id', '=', 'keep_bottles.bottle_id')
            ->leftJoin('bottle_categories', 'bottle_categories.id', '=', 'bottles.category_id')
            ->leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->where('bottles.is_trash', 0)
            ->where('keep_bottles.is_trash', 0)
            ->get([
                'customers.id', 'keep_bottles.*', 'bottles.name as bottle_name', 'bottles.code', 'bottles.is_trash  as bottle_trash',
                'bars.name as bar_name',
            ]);
        $result = [];
        foreach (collect($keepBottleInfos) as $keepBottleInfo) {
            $keepBottleInfo->created_at = Carbon::parse($keepBottleInfo->created_at)->format('Y-m-d');
            $result[] = $keepBottleInfo;
        }
        return $result;
    }

    public function findKeepBottleCanEditByBarId($barIds, $customerId)
    {
        $keepBottleInfos = DB::table('customers')
            ->whereIn('customers.bar_id', $barIds)
            ->where('customers.id', $customerId)
            ->join('keep_bottles', 'keep_bottles.customer_id', '=', 'customers.id')
            ->join('bottles', 'bottles.id', '=', 'keep_bottles.bottle_id')
            ->join('bars', 'customers.bar_id', '=', 'bars.id')
            ->where('bottles.is_trash', 0)
            ->where('keep_bottles.is_trash', 0)
            ->get([
                'customers.id', 'keep_bottles.*', 'bottles.name as bottle_name', 'bottles.code', 'bottles.is_trash  as bottle_trash',
                'bars.name as bars_name',
            ]);
        $result = [];
        foreach (collect($keepBottleInfos) as $keepBottleInfo) {
            $keepBottleInfo->created_at = Carbon::parse($keepBottleInfo->created_at)->format('Y-m-d');
            $result[] = $keepBottleInfo;
        }
        return $result;
    }

    public function modifyKeepBottleList($inputKeepBottleList, $customerId)
    {
        DB::beginTransaction();
        try {
            foreach ($inputKeepBottleList as $key => $keepBottle) {
                $input = array();
                $input['is_trash'] = $keepBottle['is_trash'];
                $input['bottle_id'] = $keepBottle['bottle_id'];
                $input['created_at'] = $keepBottle['created_at'];
                $input['remain'] = $keepBottle['remain'];
                $input['note'] = $keepBottle['note'];
                $input['customer_id'] = $customerId;
                $input['bar_id'] = $keepBottle['bar_id'];
                if (is_null($keepBottle['id'])) {
                    DB::table('keep_bottles')->insert($input);
                } else {
                    DB::table('keep_bottles')->where('id', $keepBottle['id'])
                        ->update($input);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * remove or restore customers: 
     * update is_trash to all customers in ids list
     * @param ids array
     * @param isTrash: 1-remove, 0-not remove
     * @author HoangNN
     */
    public function updateCustomerData($ids, $isTrash)
    {
        try {
            return DB::table('customers')->whereIn('id', $ids)
                ->update(['is_trash' => $isTrash]);
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function statisticBirthdayCustomerByBarAndMonth($barId, $month)
    {
        $customerUtil = new CustomerUtil();
        $customers =  $this->_model::whereMonth('date_of_birth', $month)
            ->where('is_trash', false)
            ->where('bar_id', $barId)
            ->get()
            ->map(function ($item) use ($customerUtil) {
                $data['customer_id'] = $item->id;
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = $customerUtil->getUrlIcon($item->icon);
                $data['date_of_birth'] = $item->date_of_birth;
                return $data;
            });
        return ['amount' => count($customers), 'customers' => $customers];
    }

    public function statisticBirthdayCustomerByBarNow($barId)
    {
        $customerUtil = new CustomerUtil();
        $customers =  $this->_model::whereDay('date_of_birth', now()->day)
            ->whereMonth('date_of_birth', now()->month)
            ->where('is_trash', false)
            ->where('bar_id', $barId)
            ->get()
            ->map(function ($item) use ($customerUtil) {
                $data['customer_id'] = $item->id;
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = $customerUtil->getUrlIcon($item->icon);
                $data['date_of_birth'] = $item->date_of_birth;
                return $data;
            });
        return ['amount' => count($customers), 'customers' => $customers];
    }

    public function findKeepBottleByBarIdAndTime($barId, $time, $limit)
    {
        $month = Carbon::createFromFormat('m-Y', $time)->month;
        $year = Carbon::createFromFormat('m-Y', $time)->year;
        $data = DB::table('bottles')
            ->select(DB::raw('count(DISTINCT keep_bottles.customer_id) as amount,bottles.name as bottle_name'))
            ->leftJoin('keep_bottles', 'keep_bottles.bottle_id', '=', 'bottles.id')
            ->leftJoin('customers', 'customers.id', '=', 'keep_bottles.customer_id')
            ->where('keep_bottles.bar_id', $barId)
            ->whereMonth('keep_bottles.created_at', $month)
            ->whereYear('keep_bottles.created_at', $year)
            ->where('bottles.is_trash', 0)
            ->where('keep_bottles.is_trash', 0)
            ->where('customers.is_trash', 0)
            ->groupBy('keep_bottles.bottle_id');
        if ($limit) {
            return $data->take($limit)->get();
        }
        return $data->get();
    }

    public function getRevenueCustomerByBarId($barId, $time, $limit)
    {
        $customerUtil = new CustomerUtil();
        $month = Carbon::createFromFormat('m-Y', $time)->month;
        $year = Carbon::createFromFormat('m-Y', $time)->year;
        $data = DB::table('order_histories')
            ->select(DB::raw('customers.*,SUM(order_histories.total_income) AS total_income'))
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->where('order_histories.bar_id', $barId)
            ->whereMonth('order_histories.arrival_at', $month)
            ->whereYear('order_histories.arrival_at', $year)
            ->whereNull('order_histories.deleted_at')
            ->where('customers.is_trash', 0)
            ->groupBy('order_histories.customer_id')
            ->having('total_income', '>', 0)
            ->orderBy('total_income', 'desc');
        if ($limit) {
            $data = $data->take($limit);
        }
        return $data->get()
            ->map(function ($item) use ($customerUtil) {
                $data['customer_id'] = $item->id;
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = $customerUtil->getUrlIcon($item->icon);
                $data['total_income'] = $item->total_income;
                return $data;
            });
    }

    public function getVisitCountByBarId($barId, $time, $limit)
    {
        $customerUtil = new CustomerUtil();
        $month = Carbon::createFromFormat('m-Y', $time)->month;
        $year = Carbon::createFromFormat('m-Y', $time)->year;
        $data = DB::table('order_histories')
            ->select(DB::raw('customers.*,count(order_histories.id) AS visit_count'))
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->where('order_histories.bar_id', $barId)
            ->whereMonth('order_histories.arrival_at', $month)
            ->whereYear('order_histories.arrival_at', $year)
            ->whereNull('order_histories.deleted_at')
            ->where('customers.is_trash', 0)
            ->groupBy('order_histories.customer_id')
            ->having('visit_count', '>', 0)
            ->orderBy('visit_count', 'desc');
        if ($limit) {
            $data =  $data->take($limit);
        }
        return $data->get()
            ->map(function ($item) use ($customerUtil) {
                $data['customer_id'] = $item->id;
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = $customerUtil->getUrlIcon($item->icon);
                $data['visit_count'] = $item->visit_count;
                return $data;
            });;
    }

    public function getShimeiCountByBarId($barId, $time, $limit)
    {
        $customerUtil = new CustomerUtil();
        $month = Carbon::createFromFormat('m-Y', $time)->month;
        $year = Carbon::createFromFormat('m-Y', $time)->year;
        $data = DB::table('order_histories')
            ->select(DB::raw('customers.*, COUNT(CASE
            WHEN order_histories.type = \'honshimei\' THEN 1
        END) as shimei_count'))
            ->leftJoin('customers', 'customers.id', '=', 'order_histories.customer_id')
            ->where('order_histories.bar_id', $barId)
            ->whereMonth('order_histories.arrival_at', $month)
            ->whereYear('order_histories.arrival_at', $year)
            ->whereNull('order_histories.deleted_at')
            ->where('customers.is_trash', 0)
            ->groupBy('order_histories.customer_id')
            ->having('shimei_count', '>', 0)
            ->orderBy('shimei_count', 'desc');
        if ($limit) {
            $data =  $data->take($limit);
        }
        return $data->get()
            ->map(function ($item) use ($customerUtil) {
                $data['customer_id'] = $item->id;
                $data['name'] = $item->name;
                $data['furigana_name'] = $item->furigana_name;
                $data['icon'] = $customerUtil->getUrlIcon($item->icon);
                $data['shimei_count'] = $item->shimei_count;
                return $data;
            });
    }
}
