<?php


namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\Response;
use App\Services\Impl\AppTokenServiceImpl;
use App\Services\Impl\UserTokenServiceImpl;
use App\Utils\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController
{

    /**
     * 获取token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $data = $request->only('code');

        $validator = Validator::make($data, [
            'code'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::CODE_LOST);
        }

        $userTokenService = new UserTokenServiceImpl($data['code']);
        try {
            $token = $userTokenService->get();
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, ['token' => $token]);
    }

    /**
     * 验证token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        $data = $request->only('token');

        $validator = Validator::make($data, [
            'token'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::TOKEN_LOST);
        }

        $isValid = Token::verifyToken($data['token']);

        if (!$isValid) {
            return Response::makeResponse(false, Response::TOKEN_ERROR);
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

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
