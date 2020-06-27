<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Resources\CustomerAutocompleteResource;
use App\Repositories\Bar\BarRepository;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AutocompleteController extends BaseController
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


    public function __construct(UserRepository $userRepository,
                                BarRepository $barRepository,
                                CustomerRepository $customerRepository)
    {
        $this->userRepository = $userRepository;
        $this->barRepository = $barRepository;
        $this->customerRepository = $customerRepository;
    }

    public function getCustomers(Request $request)
    {
        $user = Auth::user();
        $role = $this->getRole();
        $bar = null;
        switch ($role) {
            case UserRole::Admin:
                $barIds = $this->barRepository->findAllBarIds();
                break;
            default:
                $barIds = $this->userRepository->findAllBarIdByOwner($user);
        }
        $customers = $this->customerRepository->findCustomerByBarIds($barIds, $request->query('name'));
        if (is_null($customers)) {
            throw new NotFoundHttpException(trans('error.customer.not_found'));
        }
        return $this->sendResponse(CustomerAutocompleteResource::collection($customers), trans('api.list.success'));
    }
}
