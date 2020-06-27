<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/31/19
 * Time: 10:04 AM
 */

namespace App\Repositories\CustomerSetting;


use App\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Bar;

class CustomerSettingEloquentRepository extends EloquentRepository implements CustomerSettingRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\CustomerSetting::class;
    }

    public function findByBarId($barId)
    {
        $user = Auth::user();
        if ($user->is_admin) {
            $bar = Bar::find($barId);
        } else {
            $bar = $user->bars()->where('bar_id', $barId)->first();
            if (empty($bar)) {
                throw new ModelNotFoundException(trans('error.model.not_found'));
            }
        }
        return $bar->customerSetting()->first();
    }

    /**
     * @param $bar
     * @return mixed
     */
    public function findByBar($bar)
    {
        return $bar->customerSetting()->first();
    }

    public function updateCustomerSetting($barIds, $input)
    {
        return $this->_model::whereIn('bar_id', $barIds)->update($input);
    }

    public function getCustomerSettingByUser($barIds)
    {
        return $this->_model::whereIn('bar_id', $barIds)->get();
    }
}
