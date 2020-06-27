<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Notifications\StaffRegisterSuccess;
use App\Repositories\User\UserRepository;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerificationController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/top';

    protected $userRepository;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->middleware('auth')->except(['verify','resend']);;
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
        $this->userRepository = $userRepository;
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $user = $this->userRepository->find($request->route('id'));
        if ($user && $user->markEmailAsVerified()) {
            event(new Verified($user));
            if ($user->hasVerifiedEmail()) {
                $user->notify(new StaffRegisterSuccess);
            }
        }

        return $this->sendResponse(new UserResource($user), trans('api.verify.success'));
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return $this->sendError(trans('api.verify.verified'), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendResponse([], trans('api.verify.resend'));
    }
}