<?php


namespace App\Services\Impl;


use App\Models\UserModel;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class UserServiceImpl implements UserService
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function getById(int $uId): array
    {
        $userInfo = UserModel::where('id', '=', $uId)
            ->select('avatar', 'mobile', 'nickname')
            ->first();
        $result = resultToArray($userInfo);

//        $addresses = DB::table('user_address')
//            ->where('user_id', '=', $uId)
//            ->get();
//        $address = [];
//        if (count($addresses)) {
//            $address = resultToArray($addresses);
//        }
//
//        $result['address'] = $address;

        return $result;
    }

    public function updateById(int $uId, array $fields): void
    {
        $user = $this->model->find($uId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        unset($fields['uId']);
        $this->model->update($fields);
    }
}
