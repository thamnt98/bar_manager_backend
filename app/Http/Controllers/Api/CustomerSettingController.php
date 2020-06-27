<?php

namespace App\Http\Controllers\Api;

use App\Enums\CustomerPerPage;
use App\Enums\CustomerVisitPerPage;
use App\Enums\KeepBottleDay;
use App\Enums\OrderName;
use App\Enums\Sort;
use App\Enums\UserRole;
use App\Http\Resources\CustomerSettingResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\CustomerSetting\CustomerSettingRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;


class CustomerSettingController extends BaseController
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
     * @var CustomerSettingRepository
     */
    protected $customerSettingRepository;

    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                CustomerSettingRepository $customerSettingRepository)
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->customerSettingRepository = $customerSettingRepository;
    }

    public function getCustomerSetting($barId)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
                $bar = $this->barRepository->find($barId);
                break;
            case UserRole::Owner:
            case UserRole::Manager:
                $bar = $this->userRepository->findBarByUserAndBarId($user, $barId);
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $customerSetting = $this->customerSettingRepository->findByBar($bar);

        return $this->sendResponse(new CustomerSettingResource($customerSetting), trans('messages.setting.success'));
    }

    public function updateCustomerSetting(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        switch ($role) {
            case UserRole::Admin:
            case UserRole::Owner:
            case UserRole::Manager:
                $bar = $this->userRepository->findBarByUser($user)->pluck('id');
                break;
            default:
                throw new AccessDeniedHttpException(trans('error.access_denied'));
        }
        if (is_null($bar)) {
            throw new NotFoundHttpException(trans('error.bar.not_found'));
        }
        $validator = Validator::make($request->all(), [
            'order_name'  => [
                'bail',
                'required',
                'max:50',
                Rule::in([
                    OrderName::OptionOne,
                    OrderName::OptionTwo,
                    OrderName::OptionThree,
                    OrderName::OptionFour,
                    OrderName::OptionFive,
                    OrderName::OptionSix,
                ]),
            ],
            'order_by'  => [
                'bail',
                'required',
                'max:50',
                Rule::in([Sort::Asc, Sort::Desc]),
            ],
            'record_per_visit_page' => [
                'bail',
                'required',
                Rule::in([
                    CustomerVisitPerPage::OptionOne,
                    CustomerVisitPerPage::OptionTwo,
                    CustomerVisitPerPage::OptionThree,
                    CustomerVisitPerPage::OptionFour
                ]),
            ],
            'record_per_customer_page' => [
                'bail',
                'required',
                Rule::in([
                    CustomerPerPage::OptionOne,
                    CustomerPerPage::OptionTwo,
                    CustomerPerPage::OptionThree,
                    CustomerPerPage::OptionFour
                ]),
            ],
            'keep_bottle_day_limit' => [
                'bail',
                'required',
                Rule::in([
                    KeepBottleDay::Month,
                    KeepBottleDay::TwoMonth,
                    KeepBottleDay::ThreeMonths,
                    KeepBottleDay::SixMonths,
                    KeepBottleDay::Year,
                    KeepBottleDay::TwoYears,
                    KeepBottleDay::ThreeYears
                ]),
            ],
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $this->customerSettingRepository->updateCustomerSetting($bar, $input);
        $customerSettings = $this->customerSettingRepository->getCustomerSettingByUser($bar);
        return $this->sendResponse($customerSettings, trans('messages.setting.update'));
    }
}
