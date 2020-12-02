<?php


namespace App\Http\Controllers\Api\v1;



use App\Http\Controllers\Api\Response;
use App\Services\Impl\UserServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController
{
    public function getUser(Request $request)
    {
        $data = $request->only('uId');

        $validator = Validator::make($data, [
            'uId'          => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM);
        }

        $userService = new UserServiceImpl();
        try {
            $result = $userService->getById($data['uId']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function updateUser(Request $request)
    {
        $data = $request->only('uId', 'nickName', 'avatarUrl', 'mobile');

        $validator = Validator::make($data, [
            'uId'          => 'required|integer',
            'nickName'     => 'string',
            'avatarUrl'    => 'string',
            'mobile'       => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM);
        }

        $userService = new UserServiceImpl();
        try {
            $userService->updateById($data['uId'], $data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
