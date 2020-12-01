<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\AppTokenServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController
{
    /**
     * cms使用
     * 第三方应用获取令牌
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppToken(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET');

        $data = $request->only('username', 'password');

        $validator = Validator::make($data, [
            'username'             => 'required|string',
            'password'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $appTokenService = new AppTokenServiceImpl();

        try {
            $token = $appTokenService->get($data['username'], $data['password']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, ['token' => $token]);
    }
}
