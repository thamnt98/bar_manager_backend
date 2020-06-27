<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\AccountLimitPlan;


use App\Repositories\EloquentRepository;

class AccountLimitPlanEloquentRepository extends EloquentRepository implements AccountLimitPlanRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\AccountLimitPlan::class;
    }

    /**
     * get type account limit plan
     * @param $user
     * @return string
     */

    public function getTypeAccountLimitPlan($user)
    {
        return $this->_model::where('id',$user->limit_plan_id)->first()->type;
    }
}
