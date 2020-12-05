<?php


namespace App\Services\Impl;


use App\Models\UserAddressModel;
use App\Services\UserAddressService;

class UserAddressServiceImpl implements UserAddressService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new UserAddressModel();
    }

    public function list(array $data): array
    {
        $select = ['id', 'name', 'mobile', 'province', 'city', 'country', 'detail', 'user_id'];
        $userId = request('uId');
        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['user_id', '=', $userId];
        $orderBy = array('create_time', 'desc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        return $result;
    }

    public function add(array $fields): void
    {
        $fields['user_id'] = request('uId');
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $userAddress = $this->model->find($id);
        if (!$userAddress) {
            throw new \Exception('用户地址不存在');
        }
        if ($userAddress->delete_time != 0) {
            throw new \Exception('用户地址已删除');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $userAddress = $this->model->find($id);
        if (!$userAddress) {
            throw new \Exception('用户地址不存在');
        }
        if ($userAddress->delete_time != 0) {
            throw new \Exception('用户地址已删除');
        }
        $this->model->deleteById($id);
    }
}
