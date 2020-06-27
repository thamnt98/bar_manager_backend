<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\DebitHistory;

use App\Models\DebitHistory;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class DebitHistoryEloquentRepository extends EloquentRepository implements DebitHistoryRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\DebitHistory::class;
    }

    public function getDebitHistoriesByVisitId($visitId)
    {
        return $this->_model::where('order_id', $visitId)->get();
    }

    public function modifyDebitHistoryList($inputDebitHistoryList, $visitId)
    {
        DB::beginTransaction();
        try {
            DebitHistory::where('order_id', $visitId)->delete();
            DebitHistory::insert($inputDebitHistoryList);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
