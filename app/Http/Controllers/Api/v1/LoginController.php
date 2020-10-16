<?php


namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\Response;
use App\Services\Impl\UserTokenServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController
{

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
}
