<?php


namespace App\Http\Controllers\Api\v1;



use App\Http\Controllers\Api\Response;
use App\Services\Impl\UserServiceImpl;
use App\Utils\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController
{
    public function updateUser(Request $request)
    {
        $data = $request->only('nickName', 'avatarUrl', 'mobile');

        $validator = Validator::make($data, [
            'nickName'     => 'string',
            'avatarUrl'    => 'string',
            'mobile'       => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM);
        }

        $userService = new UserServiceImpl();
        try {
            $uId = Token::getCurrentUId();
            $userService->updateUserById($uId, $data);
        } catch (\Exception $exception) {
            if ($exception->getMessage() == '102') {
                return Response::makeResponse(false, Response::TOKEN_ERROR);
            }
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
