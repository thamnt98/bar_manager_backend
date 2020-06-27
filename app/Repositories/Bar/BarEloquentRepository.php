<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/28/19
 * Time: 1:44 PM
 */

namespace App\Repositories\Bar;

use App\Enums\UserRole;
use App\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BarEloquentRepository extends EloquentRepository implements BarRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\Bar::class;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return $this->_model::where('name', $name)->first();
    }

    /**
     * @inheritDoc
     */
    public function findUserByBar($bar)
    {
        return $bar->users()
            ->where(function (Builder $query) {
                return $query->where('role', UserRole::Manager)->orWhere('role', UserRole::Staff);
            })->get(['accounts.*', 'role']);
    }

    /**
     * @inheritDoc
     */
    public function findUserByBarAndUserId($bar, $userId)
    {
        return $bar->users()->where('account_id', $userId)
            ->where(function (Builder $query) {
                return $query->where('role', UserRole::Manager)->orWhere('role', UserRole::Staff);
            })->first(['accounts.*', 'role']);
    }

    /**
     * @return mixed
     */
    public function findAllBarIds()
    {
        return $this->_model::all()->pluck('id');
    }

    public function findAdminBar($userId)
    {
        return $this->_model::leftJoin('bar_memberships', 'bar_memberships.bar_id', '=', 'bars.id')
            ->leftJoin('accounts', 'accounts.id', '=', 'bar_memberships.account_id')
            ->where('accounts.id', '=', $userId)
            ->get();
    }

    public function findBarsOwnerByBarId($barId)
    {
        $ownerId = DB::table('bar_memberships')->leftJoin('bars', 'bars.id', '=', 'bar_memberships.bar_id')
        ->where('bar_memberships.role', '=', 'owner')
        ->where('bar_memberships.bar_id', '=', $barId)->get('bar_memberships.account_id')->pluck('account_id');

        return $this->_model::leftJoin('bar_memberships', 'bar_memberships.bar_id', '=', 'bars.id')->where('bar_memberships.account_id', $ownerId)->get();
    }
}
