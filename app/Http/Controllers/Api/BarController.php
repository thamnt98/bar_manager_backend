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
use App\Models\Bar;
use App\Repositories\AccountLimitPlan\AccountLimitPlanRepository;
use App\Repositories\Bar\BarRepository;
use Illuminate\Http\Request;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Bar as BarResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Enums\CustomerPerPage;
use App\Enums\CustomerVisitPerPage;
use App\Enums\KeepBottleDay;
use App\Enums\OrderName;
use App\Enums\Sort;
use App\Models\CustomerSetting;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BarController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var BarRepository
     */
    protected $barRepository;

    /**
     * @var AccountLimitPlanRepository
     */
    protected $accountLimitPlanRepository;

    public function __construct(
        UserRepository $userRepository,
        BarRepository $barRepository,
        AccountLimitPlanRepository $accountLimitPlanRepository
    ) {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->accountLimitPlanRepository = $accountLimitPlanRepository;
    }

    public function getDropDownBars(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Admin:
                $bars = $this->barRepository->getAll();
                break;
            default:
                $bars = $this->userRepository->findBarByUser($user);
        }
        return $this->sendResponse(BarResource::collection($bars), trans("api.list.success"), Response::HTTP_OK);
    }

    public function getListBar()
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Admin:
                $bars = $this->barRepository->getAll();
                break;
            case  UserRole::Owner:
            case  UserRole::Manager:
                $bars = $this->userRepository->findBarByUser($user);
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }

        $paginate = new PaginationResource();
        return $this->sendResponse(
            $paginate->paginate(BarResource::collection($bars)),
            trans("api.list.success")
        );
    }

    /**
     * Get list bar by user login
     *
     * @author ThamNT
     */
    public function getListBarByUserLogin()
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Admin:
            case  UserRole::Owner:
            case  UserRole::Manager:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $bars = $this->userRepository->findBarByUser($user);
        $paginate = new PaginationResource();
        return $this->sendResponse(
            $paginate->paginate(BarResource::collection($bars)),
            trans("api.list.success")
        );
    }

    public function getDetailBar(Request $request, int $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            case  UserRole::Owner:
            case  UserRole::Manager:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        return $this->sendResponse(new BarResource($bar), trans("api.detail.success"), Response::HTTP_OK);
    }


    public function createBar(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Admin:
            case  UserRole::Owner:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|required|max:255|unique:bars',
            'tel' => 'required|max:14',
            'address'  => 'bail|required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        DB::beginTransaction();
        try {
            $inputBar = collect()->all();
            $inputBar['name'] = $input['name'];
            $inputBar['tel'] = $input['tel'];
            $inputBar['address'] = $input['address'];
            $bar = $this->barRepository->create($inputBar);
            $bar->customerSetting()->save(new CustomerSetting([
                "order_name" => OrderName::OptionOne,
                "order_by" => Sort::Asc,
                "record_per_visit_page" => CustomerVisitPerPage::OptionTwo,
                "record_per_customer_page" => CustomerPerPage::OptionTwo,
                "keep_bottle_day_limit" => KeepBottleDay::Month
            ]));
            $this->userRepository->insertOwnerBarMemberships($user, $bar);
            DB::commit();
            return $this->sendResponse(new BarResource($bar), trans("auth.register.success"), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError(trans("error.bar.account"), $e->getCode(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateBar(Request $request, int $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $userBarIds = $this->userRepository->findBarByUser($user)->pluck('id')->toArray();

        if (!in_array($barId, $userBarIds) && $role != UserRole::Admin) {
            return $this->sendError(trans("error.bar.barId"));
        }
        switch ($role) {
            case UserRole::Admin:
            case UserRole::Owner:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|max:255|unique:bars,name,'.$barId,
            'tel' => 'required|max:14',
            'address'  => 'bail|required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $input['updated_at'] = DB::raw('now()');
        DB::beginTransaction();
        try {
            $bar = $this->barRepository->update($barId, $input);
            DB::commit();
            return $this->sendResponse(new BarResource($bar), trans("auth.register.success"), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError(trans("error.bar.account"), $e->getCode(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteBar($barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = $this->barRepository->find($barId);
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $userBarIds = $this->userRepository->findBarByUser($user)->pluck('id')->toArray();
        if (!in_array($barId, $userBarIds) && $role != UserRole::Admin) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        switch ($role) {
            case UserRole::Admin:
            case UserRole::Owner:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        DB::beginTransaction();
        try {
            $this->barRepository->delete($barId);
            DB::commit();
            return $this->sendResponse(new BarResource($bar), trans("api.delete.success"), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError(trans("error.bar.account"), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
