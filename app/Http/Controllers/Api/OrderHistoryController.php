<?php
/**
 * Created by PhpStorm.
 * User: huypq1
 * Date: 12/23/19
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\Api;
use App\Enums\UserRole;
use App\Http\Resources\OrderHistoryResource;
use App\Http\Resources\PaginationResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\CustomerSetting\CustomerSettingRepository;
use App\Repositories\OrderHistory\OrderHistoryRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;
use Illuminate\Validation\Rule;
use App\Enums\PayMethod;

class OrderHistoryController extends BaseController
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
     * @var OrderHistoryRepository
     */
    protected $orderHistoryRepository;

    /**
     * @var CustomerSettingRepository
     */
    protected $customerSettingRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    
    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                OrderHistoryRepository $orderHistoryRepository,
                                CustomerSettingRepository $customerSettingRepository,
                                CustomerRepository $customerRepository
                                )
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->orderHistoryRepository = $orderHistoryRepository;
        $this->customerSettingRepository = $customerSettingRepository;
        $this->customerRepository = $customerRepository;
    }

     /**
     * Get list visit by user login 
     * @param Request 
     * @param bar: array of bar's id
     * @param month: month
     * @param greater_remain_debt: remaining debt (to)
     * @param less_remain_debt: remaing debt (from)
     * @param pay_period: paymend deadline (out_date : out of date , less_than_month : less than thirty days, more_than_month : more than thirty days)
     * @param casts: array of cast's id
     * @param staffs: array of staff's id
     * @author ThamNT
     */
    public function getListVisit(Request $request)
    {
        $user = Auth::user();
        $barIds = null;
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                $barIds = $this->barRepository->getAll()->pluck('id');
                $barAdmin = $this->barRepository->findAdminBar($user->id)->pluck('bar_id');
                break;
            default:
            $barIds = $this->userRepository->findAllBarIdByOwner($user);
        }
        if ($role == UserRole::Admin) {
            $customerSetting = $this->customerSettingRepository->findByBarId($barAdmin[0]);
        } else {
            $customerSetting = $this->customerSettingRepository->findByBarId($barIds[0]);
        }
        if (!is_null($request->query('bar'))) {
            $barIds = array_map('intval', explode(',', $request->query('bar')));
        }
        $page = intval($request->query('page'));
        $orderHistories = $this->orderHistoryRepository->findOrderHistoryByListBarIds(
            $barIds,
            $request->query('month'),
            $request->query('less_remain_debt'),
            $request->query('greater_remain_debt'),
            $request->query('pay_period'),
            $request->query('cast'),
            $request->query('staff')
        );
        $paginate = new PaginationResource();
        return $this->sendResponse(
            $paginate->paginate(OrderHistoryResource::collection($orderHistories), $customerSetting->record_per_visit_page, $page)
            , trans("api.list.success"), Response::HTTP_OK);
    }

    public function getVisitDetail(Request $request, int $visitId)
    {
        $user = Auth::user();
        $orderHistory = $this->orderHistoryRepository->find($visitId);
        if (is_null($orderHistory)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $customer = $this->customerRepository->find($orderHistory->customer_id);
        if (is_null($customer) || $customer->is_trash) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $bar = null;
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($orderHistory->bar_id);
                break;
            default:
            $bar = $this->userRepository->findBarByUserAndBarId($user, $orderHistory->bar_id);
        }
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }

        $orderHistoryWithBarInfo = $this->orderHistoryRepository->findOrderHistoryByBarIdAndId($bar->id, $visitId);
        return $this->sendResponse(new OrderHistoryResource($orderHistoryWithBarInfo)
            , trans("api.list.success"), Response::HTTP_OK);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $barId = $request->bar_id;
        $staffIds = $this->userRepository->findCastOrStaffByBarId($barId, UserRole::Staff)->pluck('id')->map(function ($id) {
            return strval($id);
        })->toArray();
        $staffIds[] =null;
        $validator = Validator::make($request->all(), [
            'bar_id'  => 'bail|required',
            'customer_id' => 'bail|required',
            'arrival_time' => 'bail|required|date_multi_format:"Y/m/d H:i:s","Y-m-d H:i:s"|before_or_equal:'.now(),
            'leave_time' => 'bail|date_multi_format:"Y/m/d H:i:s","Y-m-d H:i:s"|after_or_equal:arrival_time|before_or_equal:'.now(),
            'total_income' => 'bail|required',
            'stayed_time' => 'bail|required',   
            'note' => 'bail|max:255',
            'staff_id' => Rule::in($staffIds),
            'pay_method' => ['bail', 'required', Rule::in([PayMethod::Cash, PayMethod::Card, PayMethod::Debit])],
            'pay_day' => 'bail|required_if:pay_method,debit|date_multi_format:"Y/m/d","Y-m-d"|after: yesterday',
            'debt' => 'bail|required_if:pay_method,debit|numeric',
        ]);
        $validator->after(function ($validator) use ($request, $user, $role) {
            if (empty($this->userRepository->findBarByUserAndBarId($user, $request->input('bar_id'))) && $role != UserRole::Admin) {
                $validator->addFailure('bar_id', 'not_found');
            }
        });
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = $request->all();
        $input['arrival_at'] = $input['arrival_time'];
        $input['leave_at'] = $input['leave_time'];
        $orderHistory = $this->orderHistoryRepository->create($input);
        return $this->sendResponse(new OrderHistoryResource($orderHistory), trans('api.visit.create'), Response::HTTP_CREATED);
    }

    public function update(Request $request, $visitId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $orderHistory = $this->orderHistoryRepository->find($visitId);
        if (is_null($orderHistory)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $customer = $this->customerRepository->find($orderHistory->customer_id);
        if (is_null($customer) || $customer->is_trash) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }

        $barId = $request->bar_id;
        $staffIds = $this->userRepository->findCastOrStaffByBarId($barId, UserRole::Staff)->pluck('id')->map(function ($id) {
            return strval($id);
        })->toArray();
        $staffIds[] =null;
        $validator = Validator::make($request->all(), [
            'bar_id'  => 'bail|required',
            'customer_id' => 'bail|required',
            'arrival_time' => 'bail|required|date_multi_format:"Y/m/d H:i:s","Y-m-d H:i:s"|before_or_equal:'.now(),
            'leave_time' => 'bail|date_multi_format:"Y/m/d H:i:s","Y-m-d H:i:s"|after_or_equal:arrival_time|before_or_equal:'.now(),
            'total_income' => 'bail|required',
            'total_income' => 'bail|required',
            'stayed_time' => 'bail|required',
            'note' => 'bail|max:255',
            'staff_id' => Rule::in($staffIds),
            'pay_method' => ['bail', 'required', Rule::in([PayMethod::Cash, PayMethod::Card, PayMethod::Debit])],
            'pay_day' => 'bail|required_if:pay_method,debit|date_multi_format:"Y/m/d","Y-m-d"',
            'debt' => 'bail|required_if:pay_method,debit|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $bar = null;
        $barId = $request->bar_id;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
            $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $input = $request->all();
        if (is_null($request->staff_id)) {
            $input['staff_id'] = null;
        }
        $input['arrival_at'] = $input['arrival_time'];
        $input['leave_at'] = $input['leave_time'];
        $orderHistory = $this->orderHistoryRepository->update($orderHistory->id, $input);
        return $this->sendResponse(new OrderHistoryResource($orderHistory), trans('api.visit.update'));
    }

    public function delete(Request $request, $visitId)
    {
        $user = Auth::user();
        $orderHistory = $this->orderHistoryRepository->find($visitId);
        if (is_null($orderHistory)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                break;
            case UserRole::Owner:
            case UserRole::Manager:
            case UserRole::Staff:
                $barIds = $this->userRepository->findAllBarIdByOwner($user)->toArray();
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (!$user->is_admin) {
            if (!in_array($orderHistory->bar_id, $barIds)) {
                throw new AccessDeniedHttpException(trans('error.access_denied'));
            }
        }
        $this->orderHistoryRepository->delete($orderHistory->id);
        return $this->sendResponse(new OrderHistoryResource($orderHistory), trans("api.visit.delete"));
    }
}

