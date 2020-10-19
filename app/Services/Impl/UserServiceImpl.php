<?php


namespace App\Services\Impl;


use App\Models\UserModel;
use App\Services\UserService;
use App\Utils\Token;

class UserServiceImpl implements UserService
{
    public function updateUserById(int $uId, array $data): void
    {

        $field = [];
        $nickName = $data['nickName'] ?? '';
        $avatar   = $data['avatarUrl'] ?? '';
        $mobile   = $data['mobile'] ?? '';
        if ($nickName)
            $field['nickname'] = $nickName;
        if ($avatar)
            $field['avatar'] = $avatar;
        if ($mobile)
            $field['mobile'] = $mobile;
        if (!empty($field)) {
            UserModel::where('id', '=', $uId)
                ->update($field);
        }
    }
}
