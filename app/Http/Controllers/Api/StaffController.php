<?php
/**
 * Created by PhpStorm.
 * User: huypq1
 * Date: 12/23/19
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\Api;

use App\Enums\AccountLimitPlan;
use App\Enums\UserRole;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\StaffListResourece;
use App\Repositories\Bar\BarRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Response;
use App\Http\Resources\StaffResource;
use App\Repositories\AccountLimitPlan\AccountLimitPlanRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;


class StaffController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var BarRepository
     */
    protected $barRepository;

    public function __construct(
        UserRepository $userRepository,
        BarRepository $barRepository,
        AccountLimitPlanRepository $accountLimitPlanRepository
    ) {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->accountLimitPlanRepository = $accountLimitPlanRepository;
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin :
            case UserRole::Owner :
            case UserRole::Manager:
            break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
 

        $roles = [UserRole::Staff, UserRole::Manager];
        $AdminRoles = array_merge($roles, [AccountLimitPlan::Free]);
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|required|max:255',
            'email' => 'bail|required|email|max:255|unique:accounts',
            'password' => 'bail|required|string|min:4|max:25|pwd_not_special_character',
            'bar_id' => 'required|array|min:1',
            'role' => ['required', $role == UserRole::Admin ? Rule::in($AdminRoles) : Rule::in($roles)],
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        if ($role != UserRole::Admin) {
            foreach ($input['bar_id'] as $barId) {
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
                if (is_null($bar)) {
                    throw new NotFoundHttpException(trans('error.bar.not_found'));
                }
            }
        }
        DB::beginTransaction();
        try {
            $inputUser = collect()->all();
            $inputUser['name'] = $input['name'];
            $inputUser['email'] = $input['email'];
            $inputUser['password'] = $input['password'];
            $inputUser['limit_plan_id'] = $input['role'] == AccountLimitPlan::Unlimited ? 4 : 1;
            $inputUser['creator_id'] = $user->id;
            $inputUser['invite_code'] = $this->userRepository->generateInviteCode();
            $user = $this->userRepository->create($inputUser);
            $this->insertBarMemberships($user, $input['bar_id'], $input['role']);
            event(new Registered($user));
            DB::commit();
            return $this->sendResponse(new StaffResource($user), trans("auth.register.success"), Response::HTTP_CREATED);
        } catch(\Exception $e) {
            DB::rollback();
            return $this->sendError(trans("error.bar.account"), $e->getCode(), Response::HTTP_BAD_REQUEST);
        }
    }

    function getListStaff(Request $request) {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                if (is_null($request->query('bar'))) {
                    $barIds = $this->barRepository->getAll()->pluck('id');
                } else {
                    $barIds = $this->barRepository->find($request->query('bar'))->pluck('id');
                }
                break;
            case UserRole::Owner :
            case UserRole::Manager:
            $barIds = $this->userRepository->findAllBarIdByUserAndBar($user, $request->query('bar'));
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $staffs = $this->userRepository->findStaffByBarIds($barIds, $request->query('sort'), $role);
        return $this->sendResponse((new PaginationResource())->paginate(StaffListResourece::collection($staffs)),
            trans("api.list.success"), Response::HTTP_OK);
    }

    public function getListStaffByBarId(int $barId)
    {
        $staffs = $this->userRepository->findCastOrStaffByBarId($barId, UserRole::Staff);
        return $this->sendResponse(StaffResource::collection($staffs), trans("api.list.success"), Response::HTTP_OK);
    }

    public function detail(Request $request, int $staffId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $staff = null;
        switch ($role) {
            case UserRole::Admin:
                $staff = $this->userRepository->find($staffId);
                break;
            case UserRole::Owner:
            case UserRole::Manager:
                $staff = $this->userRepository->findStaffByOwner($user->id, $staffId);
                break;
            default:
        }
        if (is_null($staff)) {
            return $this->sendResponse([], trans("api.staff.detail.success"), Response::HTTP_OK);
        }
        return $this->sendResponse(new StaffResource($staff), trans("api.staff.detail.success"), Response::HTTP_OK);
    }

    public function update(Request $request, int $staffId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $staff = null;
        switch ($role) {
            case UserRole::Admin:
                $staff = $this->userRepository->find($staffId);
                break;
            case UserRole::Owner:
            case UserRole::Manager:
                $staff = $this->userRepository->findStaffByStaffIdAndUser($staffId, $user);
                break;
            default:
        }
        if (is_null($staff)) {
            throw new NotFoundHttpException(trans('error.staff.not_found'));
        }

        $roles = [UserRole::Staff, UserRole::Manager];
        $AdminRoles = array_merge($roles, [AccountLimitPlan::Free]);
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|required|max:255',
            'new_email' => 'bail|email|max:255|unique:accounts,email|confirmed',
            'password' => 'bail|string|min:4|max:25|pwd_not_special_character|confirmed',
            'bar_id' => 'required|array|min:1',
            'role' => ['required', $role == UserRole::Admin ? Rule::in($AdminRoles) : Rule::in($roles)],
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $barIdsByUser = $this->userRepository->findAllBarIdByOwner($user)->toArray();
        if (!$user->is_admin) {
            foreach ($input['bar_id'] as $barId) {
                if (in_array($barId, $barIdsByUser)) {
                    $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
                    if (is_null($bar)) {
                        throw new NotFoundHttpException(trans('error.bar.not_found'));
                    }
                }
            }
        }
        DB::beginTransaction();
        try {
            $inputUser = collect()->all();
            $inputUser['name'] = $input['name'];
            if ($request->has('new_email')) {
                $inputUser['email'] = $input['new_email'];
            }
            if ($request->has('password')) {
                $inputUser['password'] = bcrypt($input['password']);
            }
            $inputUser['limit_plan_id'] = $input['role'] == AccountLimitPlan::Unlimited ? 4 : 1;
            $staff = $this->userRepository->update($staff->id, $inputUser);
            $barIds = $this->userRepository->findAllBarIdByOwner($staff)->toArray();
            $this->updateBarMembership($staff, $input['bar_id'], $barIds, $input['role']);
            DB::commit();
            return $this->sendResponse(new StaffResource($staff), trans("api.staff.update.success"), Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->sendError(trans("api.staff.update.fail"), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(Request $request, $staffId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $staff = null;
        switch ($role) {
            case UserRole::Admin:
                $staff = $this->userRepository->find($staffId);
                break;
            case UserRole::Owner:
            case UserRole::Manager:
                $staff = $this->userRepository->findStaffByOwner($user->id, $staffId);
                break;
                if ($this->accountLimitPlanRepository->getTypeAccountLimitPlan($user) == AccountLimitPlan::Free) {
                    throw new AccessDeniedHttpException(trans('error.access_denied'));
                }
                $staff = $this->userRepository->findStaffByOwner($user->creator_id, $staffId);
                break;
            default:
        }
        if (is_null($staff)) {
            throw new NotFoundHttpException(trans('error.staff.not_found'));
        }
        $this->userRepository->delete($staff->id);

        return $this->sendResponse(new StaffResource($staff), trans("api.staff.delete.success"), Response::HTTP_OK);
    }

    function updateBarMembership($account, $inputBm, $dbBm, $roleInput) {
        $this->insertBarMemberships($account, array_diff($inputBm, $dbBm), $roleInput);
        $this->deleteBarMemberships($account, array_diff($dbBm, $inputBm), $roleInput);
        if ($roleInput != $account->bars()->first()->pivot->first()->role) {
            $this->updateBarMemberships($account, array_diff($inputBm, array_diff($inputBm, $dbBm)), $roleInput);
        }
    }

    function insertBarMemberships($account, $barIds, $role)
    {
        $isEdit = !($role == UserRole::Staff);
        foreach ($barIds as $barId) {
            $this->userRepository->createStaffBarMemberships($account, $barId, $role, $isEdit);
        }
    }

    function updateBarMemberships($account, $barIds, $role)
    {
        $isEdit = !($role == UserRole::Staff);
        foreach ($barIds as $barId) {
            $this->userRepository->updateStaffBarMemberships($account, $barId, $role, $isEdit);
        }
    }

    function deleteBarMemberships($account, $barIds)
    {
        foreach ($barIds as $barId) {
            $this->userRepository->removeStaffBarMemberships($account, $barId);
        }
    }

     /**
     * Get list staffs by user login
     * @param Request
     * @author ThamNT
     */
    public function getDropDownStaffs(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();

        switch ($role) {
            case UserRole::Admin:
                if (is_null($request->query('bar'))) {
                    $barIds = $this->barRepository->getAll()->pluck('id');
                } else {
                    $barIds = $this->barRepository->find($request->query('bar'))->pluck('id');
                }
                break;
            case UserRole::Owner:
            case UserRole::Manager:
                $barIds = $this->userRepository->findAllBarIdByUserAndBar($user, $request->query('bar'));
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        try {
            $staffs = $this->userRepository->findStaffByListBarIds($barIds);
            return $this->sendResponse(
                StaffListResourece::collection($staffs),
                trans("api.list.success"),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->sendError($e->getCode(), trans("error.staff.list"));
        }
    }
}
