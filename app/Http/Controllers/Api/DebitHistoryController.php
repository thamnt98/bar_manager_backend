<?php

namespace App\Http\Controllers\Api;

use App\Repositories\DebitHistory\DebitHistoryRepository;
use App\Repositories\OrderHistory\OrderHistoryRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Customer\CustomerRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use App\Http\Resources\DebitHistoryResource;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Validator;

class DebitHistoryController extends BaseController
{

    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var DebitHistoryRepository
     */
    protected $debitHistory;

    /**
     * @var OrderHistoryRepository
     */
    protected $orderHistoryRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    public function __construct(
        UserRepository $userRepository,
        DebitHistoryRepository $debitHistoryRepository,
        OrderHistoryRepository $orderHistoryRepository,
        CustomerRepository $customerRepository
    ) {
        $this->userRepository = $userRepository;
        $this->debitHistoryRepository = $debitHistoryRepository;
        $this->orderHistoryRepository = $orderHistoryRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of debit history by visit id .
     * @param visitId
     * @return \Illuminate\Http\Response
     * @author ThamNT
     */
    public function getListDebitHistoryByVisit(int $visitId)
    {
        $orderHistory = $this->orderHistoryRepository->find($visitId);
        if (is_null($orderHistory)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $customer = $this->customerRepository->find($orderHistory->customer_id);
        if (is_null($customer) || $customer->is_trash) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
            case UserRole::Owner:
            case UserRole::Manager:
            case UserRole::Staff:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $debitHistoryList = $this->debitHistoryRepository->getDebitHistoriesByVisitId($visitId);
        return $this->sendResponse(
            DebitHistoryResource::collection($debitHistoryList),
            trans("api.list.success"),
            Response::HTTP_OK
        );
    }

    /**
     * Modify or create one array of debit histories by visit id .
     * @param visitId
     * @return \Illuminate\Http\Response
     * @author ThamNT
     */
    public function modifyDebitHistoryListByVisit(Request $request, int $visitId)
    {
        $orderHistory = $this->orderHistoryRepository->find($visitId);
        if (is_null($orderHistory)) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $customer = $this->customerRepository->find($orderHistory->customer_id);
        if (is_null($customer) || $customer->is_trash) {
            throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
            case UserRole::Owner:
            case UserRole::Manager:
            case UserRole::Staff:
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        $inputDebitHistoryList = $request->all()['data'];
        foreach ($inputDebitHistoryList as $key => $debitHistory) {
            $validator = Validator::make($debitHistory, [
                'pay_day' => 'bail|required|date_multi_format:"Y/m/d","Y-m-d"',
                'paid_money' => 'bail|required|numeric|gt:0',
            ]);
            if ($validator->fails()) {
                return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $inputDebitHistoryList[$key]['order_id'] = $visitId;
        }
        try {
            $this->debitHistoryRepository->modifyDebitHistoryList($inputDebitHistoryList, $visitId);
            $debitHistoryList = $this->debitHistoryRepository->getDebitHistoriesByVisitId($visitId);
            return $this->sendResponse(
                DebitHistoryResource::collection($debitHistoryList),
                trans("api.debit_history.update"),
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->sendError(trans('error.update_fail'), $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
