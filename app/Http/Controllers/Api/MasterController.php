<?php

namespace App\Http\Controllers\Api;

use App\Enums\CustomerPerPage;
use App\Enums\CustomerVisitPerPage;
use App\Enums\KeepBottleDay;
use App\Enums\OrderName;
use App\Enums\Sort;
use App\Http\Resources\JobResource;
use App\Http\Resources\PlanResource;
use App\Repositories\AccountLimitPlan\AccountLimitPlanRepository;
use App\Repositories\Job\JobRepository;

class MasterController extends BaseController
{
    /**
     * @var AccountLimitPlanRepository
     */
    protected $accountLimitPlanRepository;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    public function __construct(AccountLimitPlanRepository $accountLimitPlanRepository, JobRepository $jobRepository)
    {
        $this->accountLimitPlanRepository = $accountLimitPlanRepository;
        $this->jobRepository = $jobRepository;
    }

    public function getPlans()
    {
        $plans = $this->accountLimitPlanRepository->getAll();

        return $this->sendResponse(PlanResource::collection($plans), trans('api.list.success'));
    }

    public function getJobs()
    {
        $jobs = $this->jobRepository->getAll();

        return $this->sendResponse(JobResource::collection($jobs), trans('api.list.success'));
    }

    public function getSettings()
    {
        $orderNames = OrderName::getValues();
        $orderBy = Sort::getValues();
        $customerVisitPerPage = CustomerVisitPerPage::getValues();
        $customerPerPage = CustomerPerPage::getValues();
        $keepBottleDay = KeepBottleDay::getValues();

        return $this->sendResponse(
            array(
                'orderName' => $orderNames,
                'orderBy' => $orderBy,
                'recordPerVisitPage' => $customerVisitPerPage,
                'recordPerCustomerPage' => $customerPerPage,
                'keepBottleDayLimit' => $keepBottleDay,
                'payJpPublicKey' => env('PAYJP_PUBLIC_KEY')
            ),
            trans('api.list.success'));

    }
}
