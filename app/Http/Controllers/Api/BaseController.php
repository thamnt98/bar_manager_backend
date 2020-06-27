<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use foo\bar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = Response::HTTP_OK)
    {
        $response = [
            'isSuccess' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param array $errorMessages
     * @param int $code
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = Response::HTTP_OK)
    {
        $response = [
            'isSuccess' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function getRole()
    {
        $user = Auth::user();
        if ($user->is_admin) {
            return UserRole::Admin;
        }
        $bar = $user->bars()->first();
        return $bar->pivot->where('account_id', $user->id)->first()->role;
    }
}
