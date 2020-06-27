<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\Bottle;


use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class BottleEloquentRepository extends EloquentRepository implements BottleRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\Bottle::class;
    }

    /**
     * @inheritDoc
     */
    public function sortBottleByMultiField($barIds, $sort)
    {
        $query =  $this->_model::leftJoin('bottle_categories', 'bottle_categories.id', '=', 'bottles.category_id')
            ->leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->whereIn('bars.id', $barIds);
        if (!is_null($sort)) {
            $orders = explode(',', $sort);
            foreach ($orders as $order) {
                $orderInfo = explode('-', $order);
                if ($orderInfo[0] == 'category') {
                    $query->orderBy('bottle_categories.name', $orderInfo[1]);
                } else {
                    if ($orderInfo[0] == "serial") {
                        $sortSerial = $orderInfo[1];
                    }
                    $query->orderBy('bottles.' . $orderInfo[0], $orderInfo[1]);
                }
            }
        } else {
            $query->orderBy('bottles.serial', 'asc');
            $sortSerial = 'asc';
        }
        $sortSerial = $sortSerial ?? null;
        return (object) (['query' => $query, 'sortSerial' => $sortSerial]);
    }

    public function findBottleByListBarId($barIds, $sort)
    {
        $data = [
            'bottles.*',
            'bottle_categories.name as category_name',
            'bottle_categories.id as category_id',
            'bars.id as bar_id',
            'bars.name as bar_name'];
        $sortSerial = $this->sortBottleByMultiField($barIds, $sort)->sortSerial;
        $bottles =  $this->sortBottleByMultiField($barIds, $sort)->query;
        if (is_null($sortSerial)) {
            $bottles = $bottles->orderBy('bottles.serial', 'asc');
        }
        return $bottles->orderBy('bottles.created_at', 'asc')->get($data);
    }

    /**
     * @inheritDoc
     */
    public function isNameDuplicated($name, $id, $categoryId)
    {
        if ($id == null) {
            return $this->_model::where('name', $name)->where('category_id', $categoryId)->first();
        }
        return $this->_model::where('id', '!=', $id)->where('name', $name)->where('category_id', $categoryId)->first();
    }

    /**
     * @inheritDoc
     */
    public function isSerialDuplicated($serial, $id, $categoryId)
    {
        if ($id == null) {
            return $this->_model::where('serial', $serial)->where('category_id', $categoryId)->first();
        }
        return $this->_model::where('id', '!=', $id)->where('serial', $serial)->where('category_id', $categoryId)->first();
    }

    public function getSerialForDuplicateByCategory($categoryId)
    {
        $serial_arr = array();
        $serial_arr[] =   $this->_model::where('serial', '!=', 0)->where('category_id', $categoryId)->pluck('serial')->map(function ($serial) use ($categoryId) {
            return $categoryId . '.' . $serial;
        })->toArray();
        return $serial_arr[0];
    }

    public function getNameForDuplicateByCategory($categoryId)
    {
        $name_arr = array();
        $name_arr[] =   $this->_model::where('category_id', $categoryId)->pluck('name')->map(function ($name) use ($categoryId) {
            return $categoryId . '.' . $name;
        })->toArray();
        return $name_arr[0];
    }

    /**
     * @inheritDoc
     */
    public function deleteListBottle($bottleId)
    {
        $this->_model::whereIn('id', $bottleId)->delete();
    }

    /**
     * @inheritDoc
     */
    public function findBottleByBarId($barId)
    {
        return $this->_model::leftJoin('bottle_categories', 'bottle_categories.id', '=', 'bottles.category_id')
            ->leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->where('bottle_categories.bar_id', '=', $barId)
            ->where('bottles.is_trash', '=', 0)
            ->where('bottle_categories.is_trash', '=', 0)
            ->get(['bottles.*',
                'bottle_categories.name as category_name',
                'bottle_categories.id as category_id',
                'bars.id as bar_id',
                'bars.name as bar_name']);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function modifyBottleList($inputBottleList)
    {
        DB::beginTransaction();
        try {
            foreach($inputBottleList as $key => $bottle) {
                $input = array();
                $input['name'] = $bottle['name'];
                $input['is_trash'] = $bottle['is_trash'];
                $input['serial'] = $bottle['serial'];
                $input['category_id'] = $bottle['category_id'];
                if (is_null($bottle['id'])) {
                    $input['code'] = $bottle['pre_insert_id'];
                    $this->create($input);
                } else {
                    $this->update($bottle['id'], $input);
                }
            }
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getAllBottles()
    {
        return $this->_model::leftJoin('bottle_categories', 'bottle_categories.id', '=', 'bottles.category_id')
            ->leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->whereNull('bars.deleted_at')
            ->where('bottle_categories.is_trash', '=', 0)
            ->get(['bottles.*',
                'bottle_categories.name as category_name',
                'bottle_categories.id as category_id',
                'bars.id as bar_id',
                'bars.name as bar_name']);
    }

    public function findBottlesBarsOwnerByBarId($barId)
    {
        $ownerId = DB::table('bar_memberships')->leftJoin('bars', 'bars.id', '=', 'bar_memberships.bar_id')
            ->where('bar_memberships.role', '=', 'owner')
            ->where('bar_memberships.bar_id', '=', $barId)->get('bar_memberships.account_id')->pluck('account_id');
        return $this->_model::leftJoin('bottle_categories', 'bottle_categories.id', '=', 'bottles.category_id')
            ->leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->leftJoin('bar_memberships', 'bar_memberships.bar_id', '=', 'bars.id')->where('bar_memberships.account_id', $ownerId)->get([
                'bottles.*', 'bars.id as bar_id',
                'bars.name as bar_name'
            ]);
    }
}
