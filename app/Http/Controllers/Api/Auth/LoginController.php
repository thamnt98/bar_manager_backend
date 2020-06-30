<?php

/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/23/19
 * Time: 2:48 PM
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\LoginSuccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;
use Validator;

class LoginController extends BaseController
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'bail|required|string|min:4|max:25|pwd_not_special_character'
        ]);
        if ($validator->fails()) {
            return $this->sendError(trans("validation.validation_error"), $validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $tokenResult = $user->createToken(config('app.name'));
            $token = $tokenResult->token;
            if ($request->remember_me)
                $token->expires_at = Carbon::now()->addDays(30);
            $token->save();
            $tokenResult->user = $user;
            return $this->sendResponse(new LoginSuccess($tokenResult), trans("auth.login.success"), Response::HTTP_OK);
        } else {
            return $this->sendError(trans("auth.login.failed"), array('password' => [trans('auth.failed')]), Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $id = (new Parser())->parse($bearerToken)->getClaim('jti');
        $token = $request->user()->tokens->find($id);
        $token->revoke();

        return $this->sendResponse(null, trans("auth.logout.success"), Response::HTTP_OK);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $bearerToken = $request->bearerToken();
        $id = (new Parser())->parse($bearerToken)->getClaim('jti');
        $currentToken = $user->tokens->find($id);

        $tokenResult = $user->createToken(config('app.name'));
        $newToken = $tokenResult->token;
        $newToken->expires_at = $currentToken->expires_at;
        $newToken->save();
        $currentToken->revoke();
        $tokenResult->user = $user;

        return $this->sendResponse(new LoginSuccess($tokenResult), trans("auth.login.success"), Response::HTTP_OK);
    }
}
