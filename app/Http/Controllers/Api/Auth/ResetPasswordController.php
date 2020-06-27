<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\PasswordResetResource;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\Repositories\PasswordReset\PasswordResetRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\DB;
use Validator;

class ResetPasswordController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    /**
     * @var PasswordResetRepository
     */
    protected $passwordResetRepository;


    public function __construct(UserRepository $userRepository, PasswordResetRepository $passwordResetRepository)
    {
        $this->userRepository = $userRepository;
        $this->passwordResetRepository = $passwordResetRepository;
    }

    public function emailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email|max:255',

        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->userRepository->findByEmail($request->email);
        if (!$user) {
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.resetPassword.email.verification')]), Response::HTTP_NOT_FOUND);
        }

        $passwordReset = $this->passwordResetRepository->createOrUpdate($user->email);
        if ($passwordReset) {
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }

        return $this->sendResponse(new PasswordResetResource($passwordReset), trans('auth.reset.password.email_success'));
    }

    public function find($token)
    {
        $passwordReset = $this->passwordResetRepository->findByToken($token);
        if (!$passwordReset) {
            return $this->sendError(trans("validation.validation_error"), array('token' => [trans('validation.resetPassword.token.verification')]), Response::HTTP_NOT_FOUND);
        }
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(30)->isPast()) {
            $this->passwordResetRepository->delete($passwordReset->id);
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.resetPassword.token.invalid')]), Response::HTTP_NOT_FOUND);
        }

        return $this->sendResponse(new PasswordResetResource($passwordReset), trans('auth.reset.password.token_success'));
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email|max:255',
            'password' => 'bail|required|string|min:4|max:25|pwd_not_special_character|confirmed',
            'token' => 'bail|required|string'

        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $passwordReset = $this->passwordResetRepository->findByEmailAndToken($request->email, $request->token);
        if (!$passwordReset) {
            return $this->sendError(trans("validation.validation_error"), array('token' => [trans('validation.resetPassword.token.verification')]), Response::HTTP_NOT_FOUND);
        }
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(30)->isPast()) {
            $this->passwordResetRepository->delete($passwordReset->id);
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.resetPassword.token.invalid')]), Response::HTTP_NOT_FOUND);
        }

        $user = $this->userRepository->findByEmail($request->email);
        if (!$user) {
            return $this->sendError(trans("validation.validation_error"), array('email' => [trans('validation.resetPassword.email.verification')]), Response::HTTP_NOT_FOUND);
        }
        DB::transaction(function () use ($user, $passwordReset, $request) {
            $this->userRepository->update($user->id, array('password' => bcrypt($request->password)));
            $this->passwordResetRepository->delete($passwordReset->id);

            $user->notify(new PasswordResetSuccess);
        });

        return $this->sendResponse(new UserResource($user), trans('auth.reset.password.reset_success'));
    }
}
