<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/23/19
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\RegisterMailResource;
use App\Notifications\RegisterRequest;
use App\Notifications\RegisterSuccess;
use App\Repositories\AccountLimitPlan\AccountLimitPlanRepository;
use App\Repositories\Job\JobRepository;
use App\Repositories\RegisterMail\RegisterMailRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Response;
use App\Repositories\User\UserRepository;
use App\Services\CustomerService;
use Exception;
use Validator;

class RegisterController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var RegisterMailRepository|\App\Repositories\Repository
     */
    protected $registerMailRepository;

    /**
     * @var AccountLimitPlanRepository
     */
    protected $accountLimitPlanRepository;

    /**
     * @var CustomerService
     */
    protected $customerService;

    public function __construct(UserRepository $userRepository,
                                RegisterMailRepository $registerMailRepository,
                                AccountLimitPlanRepository $accountLimitPlanRepository,
                                CustomerService $customerService)
    {
        $this->userRepository = $userRepository;
        $this->registerMailRepository = $registerMailRepository;
        $this->accountLimitPlanRepository = $accountLimitPlanRepository;
        $this->customerService = $customerService;
    }

    public function verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:accounts',

        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $registeredMail = $this->registerMailRepository->createOrUpdate($request->email);
        if ($registeredMail) {
            try{
            $registeredMail->notify(new RegisterRequest);
            }
            catch(Exception $e)
            {
                return $this->sendError($e->getMessage(), trans("auth.register.failed"), Response::HTTP_BAD_REQUEST);
            }
        }
        return $this->sendResponse(new RegisterMailResource($registeredMail), trans("auth.register.success"), Response::HTTP_OK);
    }

    public function verificationCode($code)
    {
        $registerMail = $this->registerMailRepository->findByGeneratedCode($code);
        if (!$registerMail) {
            return $this->sendError(trans("validation.validation_error"), array('generated_code' => [trans('validation.custom.generated_code.math_email')]), Response::HTTP_NOT_FOUND);
        }
        if (Carbon::parse($registerMail->updated_at)->addMinutes(30)->isPast()) {
            $this->registerMailRepository->delete($registerMail->id);
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.custom.generated_code.expired')]), Response::HTTP_NOT_FOUND);
        }

        return $this->sendResponse(new RegisterMailResource($registerMail), trans("auth.register.confirm_code"));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'bail|required|max:255',
            'email' => 'bail|required|email|max:255|unique:accounts',
            'password' => 'bail|required|string|min:4|max:25|pwd_not_special_character',
            'generated_code' => 'required|max:255',
            'bar_name' => 'required|unique:bars,name',
            'tel' => 'required|max:14',
            'address'  => 'bail|required|max:255',
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // check token
        $registerMail = $this->registerMailRepository->findByEmailAndGeneratedCode($request->input('email'), $request->input('generated_code'));
        if (!$registerMail) {
            return $this->sendError(trans("validation.validation_error"), array('generated_code' => [trans('validation.custom.generated_code.math_email')]), Response::HTTP_NOT_FOUND);
        }
        if (Carbon::parse($registerMail->updated_at)->addMinutes(30)->isPast()) {
            $this->registerMailRepository->delete($registerMail->id);
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.custom.generated_code.expired')]), Response::HTTP_NOT_FOUND);
        }

        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $input['address'] = $request['address'];
        $input['limit_plan_id'] = 1;
        $input['password'] = bcrypt($input['password']);
        $input['invite_code'] = $this->userRepository->generateInviteCode();
        $input['email_verified_at'] = Carbon::now();
        $user = $this->userRepository->createOwner($input);
        if ($user) {
            $user->notify(new RegisterSuccess);
            $this->registerMailRepository->delete($registerMail->id);
        }
        return $this->sendResponse(new UserResource($user), trans("auth.register.success"), Response::HTTP_CREATED);
    }

    /**
     * create payjp token by card_number, card_month, card_year
     * @author HoangNN
     */
    public function createToken(Request $request)
    {
        try {
            $input = $request->all();

            $params = [
                'card' => [
                    "number" => $input['card_number'],
                    "exp_month" => $input['card_month'],
                    "exp_year" => $input['card_year'],
                ]
            ];
            $token = $this->customerService->getTokenPayJP($params);

            return $this->sendResponse(['token' => $token->id], trans("auth.register.success"), Response::HTTP_OK);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
