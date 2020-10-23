<?php


namespace App\Services\Impl;


use App\Models\UserModel;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class UserServiceImpl implements UserService
{
    public function getUserById(int $uId): array
    {
        $userInfo = UserModel::where('id', '=', $uId)
            ->select('avatar', 'mobile', 'nickname')
            ->first();
        $result = resultToArray($userInfo);

        $addresses = DB::table('user_address')
            ->where('user_id', '=', $uId)
            ->get();
        $address = [];
        if (count($addresses)) {
            $address = resultToArray($addresses);
        }

        $result['address'] = $address;

        return $result;
    }

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
            $field['update_time'] = time();
            UserModel::where('id', '=', $uId)
                ->update($field);
        }
    }
}
