<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/24/19
 * Time: 5:02 PM
 */

namespace App\Repositories\User;

use App\Enums\CustomerPerPage;
use App\Enums\CustomerVisitPerPage;
use App\Enums\KeepBottleDay;
use App\Enums\OrderName;
use App\Enums\Sort;
use App\Enums\UserRole;
use App\Models\Bar;
use App\Models\CustomerSetting;
use App\Repositories\EloquentRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Symfony\Component\Translation\Exception\InvalidResourceException;

class UserEloquentRepository extends EloquentRepository implements UserRepository
{
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\User::class;
    }

    /**
     * find user by email
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->_model::where('email',$email)->first();
    }

    /**
     * generate invite code
     */
    public function generateInviteCode()
    {
        $code = Str::random(10);
        if($this->_model::where('invite_code', $code)->first()) {
            return $this->generateToken();
        }
        return $code;
    }

    /**
     * @param $code
     * @return int
     */
    public function findUserIdByInviteCode($code)
    {
        $user = $this->_model::where('invite_code', $code)->first();
        if (!empty($user)) {
            return $user->id;
        }
        return 0;
    }

    /**
     * @param $user
     * @param $bar
     * @return mixed|void
     */
    public function insertOwnerBarMemberships($user, $bar)
    {
        $user->bars()->attach($bar->id, ['role' => UserRole::Owner, 'can_edit' => true]);
    }

    /**
     * @param $input
     * @return mixed
     */
    public function createOwner($input)
    {
        DB::beginTransaction();
        try {
            $user = $this->create($input);
            $bar = $user->bars()->save(new Bar([
                'name' => $input['bar_name'], 'tel' => $input['tel'], 'address' => $input['address']
            ]));
            $user->bars()->sync([$bar->id => ['role' => UserRole::Owner, 'can_edit' => true]]);
            $bar->customerSetting()->save(new CustomerSetting([
                "order_name" => OrderName::OptionOne,
                "order_by" => Sort::Asc,
                "record_per_visit_page" => CustomerVisitPerPage::OptionTwo,
                "record_per_customer_page" => CustomerPerPage::OptionTwo,
                "keep_bottle_day_limit" => KeepBottleDay::Month
            ]));
            DB::commit();
            return $user;
        } catch(\Exception $e) {
            DB::rollback();
            throw new InvalidResourceException(trans('error.bad_request'));
        }
    }

    public function insertStaffBarMemberships($user, $bar, $role, $canEdit)
    {
        $user->bars()->attach($bar->id, ['role' => UserRole::getKey($role), 'can_edit' => $canEdit]);
    }

    /**
     * @inheritDoc
     */
    public function findBarByUser($user)
    {
        return $user->bars()->orderBy('bar_id', 'asc')->get();
    }

    /**
     * @inheritDoc
     */
    public function findBarByUserAndBarId($user, $barId)
    {
        return $user->bars()->where('bar_id', $barId)->first();
    }

    /**
     * @inheritDoc
     */
    public function canEditBar($user, $barId)
    {
        return $user->bars()->where('bar_id', $barId)
            ->where(function (Builder $query) {
                return $query->where('role', UserRole::Manager)->orWhere('role', UserRole::Owner);
            })->first();
    }

    /**
     * @inheritDoc
     */
    public function findAllBarIdByOwner($user)
    {
        return $user->bars()->pluck('bar_id');
    }

    /**
     * @inheritDoc
     */
    public function findUserByBarIds($barIds)
    {
        return $this->_model::whereHas('bars', function (Builder $query) use ($barIds) {
            $query->whereIn('bar_id', $barIds)->where(function (Builder $query) {
                return $query->where('role', UserRole::Manager)->orWhere('role', UserRole::Staff);
            });
        })->get();
    }

    /**
     * @param $user
     * @param $barId
     * @param $role
     * @param $canEdit
     * @return mixed
     */
    public function createStaffBarMemberships($user, $barId, $role, $canEdit)
    {
        if ($role == 'free' || $role == 'unlimited') {
            $role = 'owner';
        }
        return $user->bars()->attach($barId, ['role' => UserRole::getKey($role), 'can_edit' => $canEdit]);
    }

    /**
     * @param $user
     * @param $barId
     * @return mixed
     */
    public function findAllBarIdByUserAndBar($user, $barId)
    {
        $bars = $user->bars();
        if (!is_null($barId)) {
            $bars = $bars->where('bar_id', $barId);
        }
        return $bars->pluck('bar_id')->all();
    }

    /**
     * @inheritDoc
     */
    public function findUserOfManagerByBarIds($barIds)
    {
        return $this->_model::whereHas('bars', function (Builder $query) use ($barIds) {
            $query->whereIn('bar_id', $barIds)->where(function (Builder $query) {
                return $query->where('role', UserRole::Staff);
            });
        })->get();
    }

    /**
     * @inheritDoc
     */
    public function findUserOfAdminByBarIds($barIds)
    {
        return $this->_model::whereHas('bars', function (Builder $query) use ($barIds) {
            $query->whereIn('bar_id', $barIds)->where(function (Builder $query) {
                return $query;
            });
        })->get();
    }

    /**
     * @param $ownerId
     * @param $staffId
     * @return mixed
     */
    public function findStaffByOwner($ownerId, $staffId)
    {
        return $this->_model::where('creator_id', $ownerId)->where('id', $staffId)->first();
    }

    public function findCastByOwner($ownerId, $castId)
    {
        $userId = $this->_model::where('creator_id', $ownerId)->pluck('id')->toArray();
        array_push($userId, $ownerId);
        return $this->_model::whereIn('creator_id', $userId)->where('id', $castId)->first();
    }

    /**
     * @param $user
     * @param $barId
     * @return mixed
     */
    public function removeStaffBarMemberships($user, $barId)
    {
        $user->bars()->detach($barId);
    }

    /**
     * @param $user
     * @param $barId
     * @param $role
     * @param $canEdit
     * @return mixed
     */
    public function updateStaffBarMemberships($user, $barId, $role, $canEdit)
    {
        if ($role == 'free' || $role == 'unlimited') {
            $role = 'owner';
        }
        return $user->bars()->updateExistingPivot($barId, ['role' => UserRole::getKey($role), 'can_edit' => $canEdit]);
    }

    /**
     * @inheritDoc
     */
    public function findStaffByBarIds($barIds, $sort, $role)
    {
        $query = $this->_model::select('accounts.*', 'bar_memberships.role as role')->distinct()->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id');
        $query->whereIn('bar_memberships.bar_id', $barIds)
        ->where('accounts.is_admin','=',false);
        $query->where(function (Builder $query) use($role) {
            switch ($role) {
                case UserRole::Manager:
                    $query->where('bar_memberships.role', UserRole::Staff);
                    break;
                case UserRole::Admin:
                    $query->where('bar_memberships.role', UserRole::Staff);
                    $query->orWhere('bar_memberships.role', UserRole::Manager);
                    $query->orWhere('bar_memberships.role', UserRole::Owner);
                    break;
                default:
                    $query->where('bar_memberships.role', UserRole::Staff);
                    $query->orWhere('bar_memberships.role', UserRole::Manager);
            }
            return $query;
        });
        if (!is_null($sort)) {
            $orders = explode(',', $sort);
            foreach ($orders as $order) {
                $orderInfo = explode('-', $order);
                if ($orderInfo[0] == 'role') {
                    $query->orderBy('bar_memberships.role', $orderInfo[1]);
                } else {
                    $query->orderBy('accounts.' . $orderInfo[0], $orderInfo[1]);
                }
            }
        } else {
            $query->orderBy('accounts.name', 'desc')
                ->orderBy('bar_memberships.role', 'desc');
        }
        return $query->get();
    }

    public function findCastByBarIds($barIds, $sortData)
    {
        $cast = $this->_model::select('accounts.*', 'bar_memberships.role as role')
            ->distinct()
            ->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id')
            ->whereIn('bar_memberships.bar_id', $barIds)
            ->where('bar_memberships.role', UserRole::Cast)
            ->when(isset($sortData), function ($q) use ($sortData) {
                $sorts = array_map(function ($item) {
                    return explode("-", $item);
                }, explode(",", $sortData));

                foreach ($sorts as $sort) {
                    $q->orderBy($sort[0], $sort[1]);
                }
            })
            ->get();
        return $cast;
    }

    public function findCastInBarByCastIdAndBarIds($castId, $barIds)
    {
        return $this->_model::where('id', $castId)
            ->whereHas('bars', function ($q) use ($barIds) {
                $q->where('role', UserRole::Cast)
                    ->whereIn('bar_id', $barIds);
            })
            ->first();
    }

    public function findBarIdByUser($user)
    {
        return $user->bars()->orderBy('bar_id', 'asc')->pluck('bars.id')->toArray();
    }

    public function findCastOrStaffByBarId($barId, $role)
    {
        $cast = $this->_model::select('accounts.*', 'bar_memberships.role as role')
            ->distinct()
            ->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id')
            ->where('bar_memberships.bar_id', $barId)
            ->where('bar_memberships.role', $role)
            ->whereNotNull('accounts.email_verified_at')
            ->get();
        return $cast;
    }

    public function findStaffByStaffIdAndUser($staffId, $user)
    {
        $barIds = $user->bars()->pluck('bar_id')->toArray();
        return $this->_model::select('accounts.*')
            ->distinct()
            ->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id')
            ->whereIn('bar_memberships.bar_id', $barIds)
            ->where('bar_memberships.role', UserRole::Staff)
            ->orWhere('bar_memberships.role', UserRole::Manager)
            ->where('accounts.id', $staffId)
            ->first();
    }

    public function findStaffByListBarIds($barIds)
    {
        return $this->_model::select('accounts.*', 'bar_memberships.role as role')
            ->distinct()
            ->leftJoin('bar_memberships', 'accounts.id', '=', 'bar_memberships.account_id')
            ->whereIn('bar_memberships.bar_id', $barIds)
            ->where('bar_memberships.role', UserRole::Staff)
            ->get();
    }
}

