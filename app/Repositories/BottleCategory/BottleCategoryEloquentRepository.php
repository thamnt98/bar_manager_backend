<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\BottleCategory;


use App\Repositories\EloquentRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BottleCategoryEloquentRepository extends EloquentRepository implements BottleCategoryRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\BottleCategory::class;
    }

    public function sortBottleCategoryMultiField ($barIds, $sort)
    {
        $query =  $this->_model::leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->whereIn('bars.id', $barIds);
        if (!is_null($sort)) {
            $orders = explode(',', $sort);
            foreach ($orders as $order) {
                $orderInfo = explode('-', $order);
                $query->orderBy('bottle_categories.' . $orderInfo[0], $orderInfo[1]);
                if ($orderInfo[0] == "serial") {
                    $sortSerial = $orderInfo[1];
                }
            }
        } else {
            $query->orderBy('bottle_categories.serial', 'asc');
            $sortSerial = 'asc';
        }
        $sortSerial = $sortSerial ?? null;
        return (object) (['query' => $query, 'sortSerial' => $sortSerial]);
    }

    /**
     * @inheritDoc
     */
    public function findBottleCategoryByListBarId($barIds, $sort)
    {
        $data = ['bottle_categories.*', 'bars.name as bar_name'];
        $sortSerial = $this->sortBottleCategoryMultiField($barIds, $sort)->sortSerial;
        $bottleCategories = $this->sortBottleCategoryMultiField($barIds, $sort)->query;
        if (is_null($sortSerial)) {
            $bottleCategories = $bottleCategories->orderBy('bottle_categories.serial', 'asc');
        }
        return $bottleCategories->orderBy('bottle_categories.created_at', 'asc')->get($data);
    }

    /**
     * @inheritDoc
     */
    public function isNameDuplicated($name, $id, $barId)
    {
        if ($id == null) {
            return $this->_model::where('name', $name)->where('bar_id', $barId)->first();
        }
        return $this->_model::where('id', '!=', $id)->where('name', $name)->where('bar_id', $barId)->first();
    }

    public function getSerialForDuplicateByBar($barId)
    {
        $serial_arr = array();
        $serial_arr[] =   $this->_model::where('serial', '!=', 0)->where('bar_id', $barId)->pluck('serial')->map(function ($serial) use ($barId) {
            return $barId . '.' . $serial;
        })->toArray();
        return $serial_arr[0];
    }

    public function getNameForDuplicateByBaR($barId)
    {
        $name_arr = array();
        $name_arr[] =   $this->_model::where('bar_id', $barId)->pluck('name')->map(function ($name) use ($barId) {
            return $barId . '.' . $name;
        })->toArray();
        return $name_arr[0];
    }

    /**
     * @inheritDoc
     */
    public function isSerialDuplicated($serial, $id, $barId)
    {
        if ($id == null) {
            return $this->_model::where('serial', $serial)->where('bar_id', $barId)->first();
        }
        return $this->_model::where('id', '!=', $id)->where('serial', $serial)->where('bar_id', $barId)->first();
    }

    /**
     * @inheritDoc
     */
    public function deleteListBottleCategroy($bottleCategoryId)
    {
        $this->_model::whereIn('id', $bottleCategoryId)->delete();
    }

    /**
     * @inheritDoc
     */
    public function findBottleCategoryByBarId($barId)
    {
        return $this->_model::leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->where('bottle_categories.bar_id', '=', $barId)
            ->where('bottle_categories.is_trash', '=', 0)
            ->orderBy('bottle_categories.serial', 'ASC')
            ->get(['bottle_categories.*', 'bars.name as bar_name']);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function modifyBottleCategoryList($inputBottleCategoryList)
    {
        DB::beginTransaction();
        try {
            foreach($inputBottleCategoryList as $key => $category) {
                $input = array();
                $input['name'] = $category['name'];
                $input['is_trash'] = $category['is_trash'];
                $input['serial'] = $category['serial'];
                if (is_null($category['id'])) {
                    $input['bar_id'] = $category['bar_id'];
                    $input['display_name'] = $category['name'];
                    $this->create($input);
                } else {
                    $this->update( $category['id'], $input);
                }
            }
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function findByBarId($barId)
    {
        return $this->_model::where('bar_id', $barId)->get();
    }

    public function getAllBottleCategory()
    {
        return $this->_model::leftJoin('bars', 'bars.id', '=', 'bottle_categories.bar_id')
            ->whereNull('bars.deleted_at')->get(['bottle_categories.*']);
    }
}
