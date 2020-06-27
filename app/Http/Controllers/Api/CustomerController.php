<?php
/**
 * Created by PhpStorm.
 * User: huypq1
 * Date: 12/23/19
 * Time: 2:48 PM
 */
namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Resources\Bar as BarResource;
use App\Http\Resources\BottleResource;
use App\Http\Resources\CreateCustomerResource;
use App\Http\Resources\Customer as CustomerResource;
use App\Http\Resources\OrderHistoryResource;
use App\Http\Resources\PaginationResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\CustomerSetting\CustomerSettingRepository;
use App\Repositories\OrderHistory\OrderHistoryRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;
use App\Models\Customer;
use App\Models\KeepBottle;
use App\Repositories\Bottle\BottleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Services\CustomerService;

class CustomerController extends BaseController
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
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerSettingRepository
     */
    protected $customerSettingRepository;

    /**
     * @var OrderHistoryRepository
     */
    protected $orderHistoryRepository;

    /**
     * @var CustomerService
     */
    protected $customerService;

    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                CustomerRepository $customerRepository,
                                CustomerSettingRepository $customerSettingRepository,
                                OrderHistoryRepository $orderHistoryRepository,
                                BottleRepository $bottleRepository,
                                CustomerService $customerService
                                )
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSettingRepository = $customerSettingRepository;
        $this->orderHistoryRepository = $orderHistoryRepository;
        $this->bottleRepository = $bottleRepository;
        $this->customerService = $customerService;
    }

    public function getCustomerList(Request $request)
    {

        $user = Auth::user();
        $barIds = null;
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                $barIds = $this->barRepository->getAll()->pluck('id');
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
        }
        $page = intval($request->query('page'));
        $barsByUserLogin = $this->userRepository->findAllBarIdByOwner($user);
        if (is_null($barsByUserLogin)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $customerSetting = $this->customerSettingRepository->findByBarId($barsByUserLogin[0]);
        $keepBottleInfos = $this->customerRepository->findKeepBottlesByListBarId($barIds,$request->query('keep_day'), $request->query('bottle'), $customerSetting->keep_bottle_day_limit);
        $customers = $this->customerRepository->findCustomerByListBarId($barIds, $keepBottleInfos,
            $request->query('search'),
            $request->query('bar'),
            $request->query('favorite_rank'),
            $request->query('income_rank'),
            $request->query('must_greater_date_of_birth'),
            $request->query('must_less_date_of_birth'),
            $customerSetting->order_name,
            $customerSetting->order_by,
            $request->query('sort'));
        if (!is_null($request->query('keep_day')) || !is_null($request->query('bottle'))) {
            $customers = $customers->reject(function ($item) {
                return $item['bottles'] == [];
            });
        }
        $sort = $request->query('sort');
        if (!is_null($sort) && strpos($sort, 'bottle_name') !== false) {
            $sortList = explode(",", $sort);
            foreach ($sortList as $sortItem) {
                $sortInfo = explode("-", $sortItem);
                if ($sortInfo[0] == 'bottle_name') {
                    $customers = $this->sortCustomerListByBottleName($customers, $sortInfo[1]);
                }
            }
        }
        $paginate = new PaginationResource();
        return $this->sendResponse($paginate->paginate($customers, $customerSetting->record_per_customer_page, $page)
            , trans("api.list.success"), Response::HTTP_OK);
    }


    /**
     * get customer data
     * @param remove_flag: 0-All, 1-Deleted, 2-not Deleted
     * @author HoangNN
     */
    public function getCustomerData(Request $request)
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
                break;
        }
        if ($role == UserRole::Admin) {
            $customerSetting = $this->customerSettingRepository->findByBarId($barAdmin[0]);
        } else {
            $customerSetting = $this->customerSettingRepository->findByBarId($barIds[0]);
        }
        $keepBottleInfos = $this->customerRepository->findKeepBottlesByListBarId($barIds,$request->query('keep_day'), $request->query('bottle'), $customerSetting->keep_bottle_day_limit);
        $customers = $this->customerRepository->findCustomerDataByListBarId($barIds, $keepBottleInfos,
            $request->query('search'),
            $request->query('bar'),
            $request->query('favorite_rank'),
            $request->query('income_rank'),
            $request->query('must_greater_date_of_birth'),
            $request->query('must_less_date_of_birth'),
            $customerSetting->order_name,
            $customerSetting->order_by,
            $request->query('sort'),
            $request->query('remove_flag'));

        if (!is_null($request->query('keep_day')) || !is_null($request->query('bottle'))) {
            $customers = $customers->reject(function ($item) {
                return $item['bottles'] == [];
            });
        }
        $sort = $request->query('sort');
        if (!is_null($sort) && strpos($sort, 'bottle_name') !== false) {
            $sortList = explode(",", $sort);
            foreach ($sortList as $sortItem) {
                $sortInfo = explode("-", $sortItem);
                if ($sortInfo[0] == 'bottle_name') {
                    $customers = $this->sortCustomerListByBottleName($customers, $sortInfo[1]);
                }
            }
        } 
        $customers = array_values($customers->toArray());
        return $this->sendResponse($customers, trans("api.list.success"), Response::HTTP_OK);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|required|max:255',
            'date_of_birth' => 'date_multi_format:"Y/m/d","Y-m-d"|before:'. Carbon::now()->subYears(20)->format('Y-m-d') . '|after:1900-01-01',
            'bar_id' => 'required'
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
        $customer = $this->customerRepository->create($input);
        return $this->sendResponse(new CreateCustomerResource($customer), trans('api.customer.create'));
    }

    public function getCustomerDetail(Request $request, int $customerId)
    {
        $user = Auth::user();
        $customer = $this->customerRepository->find($customerId);
        if (is_null($customer) || $customer->is_trash) {
            throw new NotFoundHttpException(trans('error.customer.not_found'));
        }
        if (!$user->is_admin) {
            if (is_null($customer) || is_null($this->userRepository->findBarByUserAndBarId($user, $customer->bar_id))) {
                throw new MethodNotAllowedHttpException([], 'Forbidden');
            }
        }

        $keepBottleInfos = $this->customerRepository->findKeepBottlesByBarId($customer->id, $customer->bar_id);
        $customerProfile = $this->customerRepository->findCustomerByIdAndBarId($customer->id, $customer->bar_id, $keepBottleInfos);
        return $this->sendResponse(collect($customerProfile)->first()
            , trans("api.list.success"), Response::HTTP_OK);
    }

    public function updateCustomerDetail(Request $request, int $customerId)
    {
        $user = Auth::user();
        $customer = $this->customerRepository->find($customerId);
        if ($user->is_admin) {
            if (!is_null($user->creator_id)) {
                $user = $this->userRepository->find($user->creator_id);
            }
            if (is_null($customer)) {
                throw new MethodNotAllowedHttpException([], 'Forbidden');
            }
        }
        $barId = $customer->bar_id;
        $castIds = $this->userRepository->findCastOrStaffByBarId($barId, UserRole::Staff)->pluck('id')->map(function ($id) {
            return strval($id);
        })->toArray();
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|filled|max:255',
            "furigana_name" => 'bail|filled|max:255',
            "icon" => ['regex:/.+\.(png|jpg|jpeg|gif|png|svg)$/'],
            "company_name" => 'max:255',
            "age"=> 'numeric',
            "date_of_birth" => 'date_multi_format:"Y/m/d","Y-m-d"date_multi_format:"Y/m/d","Y-m-d"',
            "email" => 'email',
            "post_no" => 'regex:/^\d{3}[-]\d{4}/i',
            "favorite_rank" => 'numeric|min:0|max:5',
            "income_rank" => 'numeric|min:0|max:5',
            "in_charge_cast_id" => Rule::in($castIds),
            "friends" => 'string',
            "note" => 'string|max:255',
            "phone_number" => 'string|max:14'
        ]);

        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        if (is_null($request->in_charge_cast_id)) {
            $input['in_charge_cast_id'] = null;
        }
        $customer = $this->customerRepository->update($customerId, $input);
        return $this->sendResponse(new CustomerResource($customer), trans("auth.register.success"), Response::HTTP_OK);
    }

    /**
     * update customer data
     * @author HoangNN
     */
    public function updateCustomerData(Request $request)
    {
        $user = Auth::user();    
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Staff:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $validator = Validator::make($request->all(), [
            'ids'  => 'required',
            'is_trash'  => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $ids = explode(',', $input['ids']);
        $is_trash = $input['is_trash'] == '1' ? true : false;
  
        $countRemoved = $this->customerRepository->updateCustomerData($ids, $is_trash);
        
        return $this->sendResponse($countRemoved, $is_trash ? trans("api.delete.success") : trans("api.restore.success"), Response::HTTP_OK);
    }

    public function getListVisitByCustomer(Request $request, int $customerId)
    {
        $user = Auth::user();
        $orders = null;
        $customer = $this->customerRepository->find($customerId);
        if (is_null($customer) || $customer->is_trash) {
            throw new NotFoundHttpException(trans('error.customer.not_found'));
        }
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($customer->bar_id);
                $barAdmin = $this->barRepository->findAdminBar($user->id)->pluck('bar_id');
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $customer->bar_id);
                break;
        }
        if ($role == UserRole::Admin) {
            $customerSetting = $this->customerSettingRepository->findByBarId($barAdmin[0]);
        } else {
            $customerSetting = $this->customerSettingRepository->findByBarId($bar->id);
        }
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $page = intval($request->query('page'));
        $orders = $this->orderHistoryRepository->findOrderHistoryByCustomerId($customer->id , $request->query('month'));
        $paginate = new PaginationResource();
        return $this->sendResponse(
            $paginate->paginate(OrderHistoryResource::collection($orders), $customerSetting->record_per_visit_page, $page)
            , trans("api.list.success"), Response::HTTP_OK);
    }

    public function getCustomerReport(Request $request, int $customerId)
    {
        $user = Auth::user();
        $orders = null;
        $customer = $this->customerRepository->find($customerId);
        if (is_null($customer)) {
            throw new NotFoundHttpException(trans('error.customer.not_found'));
        }
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($customer->bar_id);
                break;
           default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $customer->bar_id);
                break;
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $revenue = $this->orderHistoryRepository->reportRevenueOrderHistoryByCustomerId($customer->id, $request->query('month'));
        if ($request->has('month')) {
            $column = $this->orderHistoryRepository->reportChartColumnOrderHistoryByCustomerId($customer->id, $request->query('month'));
            $doughnut = $this->orderHistoryRepository->reportChartDoughnutOrderHistoryByCustomerId($customer->id, $request->query('month'));
            return $this->sendResponse(collect([
                'revenue' => $revenue,
                'column' => $column,
                'doughnut' => $doughnut,
            ]), trans("api.report.success"), Response::HTTP_OK);
        }
        return $this->sendResponse(collect(['revenue' => $revenue]), trans("api.report.success"), Response::HTTP_OK);

    }

    public function getCustomerReportByBar(Request $request, int $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $revenue = $this->orderHistoryRepository->reportRevenueOrderHistoryByBarId($barId, $request->query('month'));
        if ($request->has('month')) {
            $column = $this->orderHistoryRepository->reportChartColumnOrderHistoryByBarId($barId, $request->query('month'));
            $doughnut = $this->orderHistoryRepository->reportChartDoughnutOrderHistoryByBarId($barId, $request->query('month'));
            return $this->sendResponse(collect([
                'revenue' => $revenue,
                'column' => $column,
                'doughnut' => $doughnut,
            ]), trans("api.report.success"), Response::HTTP_OK);
        }
        return $this->sendResponse(collect(['revenue' => $revenue]), trans("api.report.success"), Response::HTTP_OK);
    }

    private function sortCustomerListByBottleName($customers, $orderType)
    {
        for ($i = 0; $i < count($customers) - 1; $i++)
            for ($j = $i + 1; $j < count($customers); $j++)
                if ($this->hasLessOrder($customers[$i], $customers[$j], $orderType)) {
                    $temp = $customers[$i];
                    $customers[$i] = $customers[$j];
                    $customers[$j] = $temp;
                }
        return $customers;
    }

    private function hasLessOrder($customerA, $customerB, $orderType)
    {
        if (count($customerA['bottles']) == 0 && count($customerB['bottles']) > 0) {
            return true;
        }
        if (count($customerB['bottles']) == 0) {
            return false;
        }
        if ($orderType == 'asc') {
            if ($customerA['bottles'][0]->name > $customerB['bottles'][0]->name) {
                return true;
            }
        } else {
            if ($customerA['bottles'][0]->name < $customerB['bottles'][0]->name) {
                return true;
            }
        }
        return false;
    }

    public function importCSVCustomers(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $validator = Validator::make($request->all(), [
            'csv_file'  => 'required|mimes:csv,txt',
            'bar_id' => 'required',
        ]);
        $validator->after(function ($validator) use ($request, $user, $role) {
            if (empty($this->userRepository->findBarByUserAndBarId($user, $request->input('bar_id'))) && $role != UserRole::Admin) {
                $validator->addFailure('bar_id', 'not_found');
            }
        });
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), trans("validation.validation_error"), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        array_shift($data);
        if (empty($data)) {
            return $this->sendError(trans("api.csv.import.empty"), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $data = $this->customerRepository->separateCsvToCustomersAndKeepBottles($data, $request);
            $customerData = $data['customers'];
            $keepBottleData = $data['keepBottles'];
            Customer::insert($customerData);
            $count = count($customerData);
            $customerIds = Customer::orderBy('id', 'desc')->take($count)->pluck('id')->toArray();
            foreach ($keepBottleData as $index => &$keepBottle) {
                $keepBottle['customer_id'] = $customerIds[$count - 1 - $index];
            }
            KeepBottle::insert($keepBottleData);
            return $this->sendResponse([], trans("api.csv.import.success"), Response::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), trans("api.csv.import.fail"), Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            DB::rollback();
            return $this->sendError($e->validator, trans("api.csv.import.fail"), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), trans("api.csv.import.fail"), Response::HTTP_BAD_REQUEST);
        }
     }
    public function listKeepBottle(Request $request, int $customerId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $BarIds = $this->userRepository->findBarIdByUser($user);
        $listKeepBottle = $this->customerRepository->findKeepBottleByCustomer($customerId);
        switch ($role) {
            case  UserRole::Admin :
                $listKeepBottleEdit = $listKeepBottle;
                break;
            default:
            $listKeepBottleEdit = $this->customerRepository->findKeepBottleCanEditByBarId($BarIds,$customerId);
        }
        $response =  [
            'isSuccess' => true,
            'data' => $listKeepBottle,
            'is_edit' => $listKeepBottleEdit,
            'message' => trans("api.list.success"),
        ];
        return $response;
    }


    public function modifyKeepBottle(Request $request,int $customerId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case  UserRole::Staff :
                throw new AccessDeniedHttpException(trans('error.access_denied'));
                break;
            default:
                break;
        }
        $inputKeepBottleList = $request->all()['data'];
        $errorList = array();
        foreach($inputKeepBottleList as $key => $keepBottle) {
            $error = array();
            if (is_null($keepBottle['remain'])) {
                $error['remain'] = trans('validation.custom.remain.required');
            } else {
                if (!preg_match('/^[0-9０-９]{1,10}$/i', $keepBottle['remain']))
                    $error['remain'] = trans('validation.custom.remain.not_number');
            }
            if (empty($keepBottle['created_at'])) {
                $error['created_at'] = trans('validation.custom.created_at.required');
            } else {
                if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$keepBottle['created_at'])) {
                    $error['created_at'] = trans('validation.custom.created_at.date_multi_format');
                }
            }
            if (empty($keepBottle['bar_id'])) {
                $error['bar_id'] = trans('validation.custom.bar_id.required');
            }else{
                if(empty($this->userRepository->findBarByUserAndBarId($user,$keepBottle['bar_id'])) && $role != UserRole::Admin){
                    $error['bar_id'] = trans('validation.custom.bar_id.not_found');
                }
            }
            if (!preg_match('/^.{0,255}$/', $keepBottle['note'])) {
                $error['note'] = trans('validation.custom.note.max_value');
            }
            if (count($error) > 0) {
                if (is_null($keepBottle['id'])) {
                    $errorList[$keepBottle['pre_insert_id']] = $error;
                } else {
                    $errorList[$keepBottle['id']] = $error;
                }
            }
        }
        if (count($errorList) > 0) {
            return $this->sendError(trans('error.bad_request'), $errorList, Response::HTTP_BAD_REQUEST);
        }
        try {
            $this->customerRepository->modifyKeepBottleList($inputKeepBottleList,$customerId);
            return $this->sendResponse($request->all(), trans("api.keep_bottle.update"), Response::HTTP_OK);
        } catch(\Exception $e) {
            return $this->sendError(trans('error.update_fail'), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    }

    public function getDropDownBarsByCustomer(Request $request, int $customerId)
    {
        $user = Auth::user();
        if($user->is_admin) {
            $customer = $this->customerRepository->find($customerId);
            $bars = $this->barRepository->findBarsOwnerByBarId($customer->bar_id);
            return $this->sendResponse(BarResource::collection($bars), trans("api.list.success"), Response::HTTP_OK);
        }
        $bars = $this->userRepository->findBarByUser($user);
        return $this->sendResponse(BarResource::collection($bars)
            , trans("api.list.success"));
    }

    public function getListBottlesBarsByCustomer(Request $request, int $customerId)
    {
        $user = Auth::user();
        $customer = $this->customerRepository->find($customerId);
        if (is_null($customer)) {
            throw new NotFoundHttpException(trans('error.customer.not_found'));
        }
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($customer->bar_id);
                break;
            default:
            $bar = $this->userRepository->findBarByUserAndBarId($user, $customer->bar_id);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $bottles = $this->bottleRepository->findBottleByBarId($customer->bar_id);
        return $this->sendResponse(BottleResource::collection($bottles), trans("api.list.success"), Response::HTTP_OK);
    }

    /**
     * Upload Avatar for customer
     * @param customerId, request:icon
     * @author ThamNT
     */
    public function uploadAvatar(Request $request, int $customerId)
    {
        $customer = $this->customerRepository->find($customerId);
        if (is_null($customer) || $customer->is_trash) {
            throw new MethodNotAllowedHttpException([], 'Forbidden');
        }
        $validator = Validator::make($request->all(), [
            "icon" => 'required|image|mimes:png,jpg,jpeg|max:5120',     
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $icon = $this->customerService->handleUploadedFile($request->file('icon'));
        try {
            $customer = $this->customerRepository->update($customerId, ['icon' => $icon]);
            return $this->sendResponse(new CustomerResource($customer), trans("api.upload_file.success"), Response::HTTP_OK);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Statistic birthday for customer by Bar today, this month and next month
     * @param barId
     * @author ThamNT
     */
    public function getBirthdayCustomerStatisticByBar(Request $request,$barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if ($request->has('month')) {
            $month = $request->month;
        } else {
            $month = now()->month;
        }
        return $this->sendResponse(collect([
            'today' => $this->customerRepository->statisticBirthdayCustomerByBarNow($barId),
            'this_month' => $this->customerRepository->statisticBirthdayCustomerByBarAndMonth($barId, $month),
            'next_month' => $this->customerRepository->statisticBirthdayCustomerByBarAndMonth($barId, $month + 1)
        ]), trans("api.report.success"), Response::HTTP_OK);
    }

    /** 
     * Get statistic of customer by keep bottle and bar 
     * @param barId, request:month
     * @author ThamNT
     */
    public function getKeepBottleCustomerStatisticByBar(Request $request, $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($request->month)) {
            $month = date("m-Y");
        } else {
            $month = $request->month;
        }
        if (is_null($request->limit)) {
            $limit = 5;
        } else {
            $limit = $request->limit;
        }
        $keepBottles = $this->customerRepository->findKeepBottleByBarIdAndTime($barId, $month, $limit);
        return $this->sendResponse(['month' => $month, 'keep_bottles' => $keepBottles], trans("api.report.success"), Response::HTTP_OK);
    }

     /** 
     * Get statistic revenue of customer by bar 
     * @param barId, request:month,limit
     * @author ThamNT
     */
    public function statisticRevenueCustomersByBar(Request $request, $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($request->month)) {
            $month = date("m-Y");
        } else {
            $month = $request->month;
        }
        if (is_null($request->limit)) {
            $limit = 5;
        } else {
            $limit = $request->limit;
        }
        $revenue = $this->customerRepository->getRevenueCustomerByBarId($barId, $month, $limit);
        return $this->sendResponse(['month' => $month, 'revenue' => $revenue], trans("api.report.success"), Response::HTTP_OK);
    }
    
     /** 
     * Get statistic counting visit of customer by bar 
     * @param barId, request:month,limit
     * @author ThamNT
     */
    public function statisticCountVisitCustomersByBar(Request $request, $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($request->month)) {
            $month = date("m-Y");
        } else {
            $month = $request->month;
        }
        if (is_null($request->limit)) {
            $limit = 5;
        } else {
            $limit = $request->limit;
        }
        $visitCount = $this->customerRepository->getVisitCountByBarId($barId, $month, $limit);
        return $this->sendResponse(['month' => $month, 'visit_count' => $visitCount], trans("api.report.success"), Response::HTTP_OK);
    }

     /** 
     * Get statistic counting type honshimei of customer by bar 
     * @param barId, request:month,limit
     * @author ThamNT
     */
    public function statisticCountShimeiCustomersByBar(Request $request, $barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            default:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
        }
        if (is_null($bar)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($request->month)) {
            $month = date("m-Y");
        } else {
            $month = $request->month;
        }
        if (is_null($request->limit)) {
            $limit = 5;
        } else {
            $limit = $request->limit;
        }
        $visitCount = $this->customerRepository->getShimeiCountByBarId($barId, $month, $limit);
        return $this->sendResponse(['month' => $month, 'shimei_count' => $visitCount], trans("api.report.success"), Response::HTTP_OK);
    }
}
