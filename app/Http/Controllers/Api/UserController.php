<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/23/19
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\Api;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User as UserResource;
use Validator;

class UserController extends BaseController
{
    /**
     * @var UserRepository|\App\Repositories\Repository
     */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function details()
    {
        $user = Auth::user();
        return $this->sendResponse(new UserResource($user), trans('api.user.detail'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'new_email' => 'bail|email|max:255|unique:accounts,email|confirmed',
            'password' => 'bail|string|min:4|max:25|pwd_not_special_character|confirmed',
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $input = $request->all();
        $inputUser = collect()->all();
        if ($request->has('name')) {    
            $inputUser['name'] = $input['name'];
        }
        if ($request->has('new_email')) {
            $inputUser['email'] = $input['new_email'];
        }
        if ($request->has('password')) {
            $inputUser['password'] = bcrypt($input['password']);
        }
        if (!empty($inputUser)) {
            $user = $this->userRepository->update($user->id, $inputUser);
        }
        return $this->sendResponse(new UserResource($user), trans('api.user.update'));
    }
}
