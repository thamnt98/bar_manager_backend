<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 10:27 PM
 */

namespace App\Repositories\AccountLimitPlan;


interface AccountLimitPlanRepository
{
    /**
     * get type account limit plan
     * @param $user
     * @return string
     */
    public function getTypeAccountLimitPlan($user);

}
