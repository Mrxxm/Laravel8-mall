<?php


namespace App\Services\Impl;


use App\Models\ThirdAppModel;
use App\Services\AdminUserService;

class AdminUserServiceImpl implements AdminUserService
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new ThirdAppModel();
    }

    public function list(array $data): array
    {
        $select = ['id', 'app_id', 'app_description', 'scope', 'scope_description', 'create_time', 'update_time', 'delete_time'];

        $keyword = $data['keyword'] ?? '';
        $status = $data['status'] ?? null;
        $conditions = [];
        if (!is_null($status)) {
            if ($status) {
                $conditions[] = ['delete_time', '=', 0];
            } else {
                $conditions[] = ['delete_time', '!=', 0];
            }
        }
        if (!empty($keyword)) {
            $conditions[] = ['app_id', 'like', "%{$keyword}%"];
        }

        $result = $this->model->list($select, $conditions);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['delete_date'] = DateFormat($res['delete_time']);
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        $fields['app_secret'] = md5(md5($fields['app_secret']));
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $adminUser = $this->model->find($id);
        if (!$adminUser) {
            throw new \Exception('用户不存在');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $adminUser = $this->model->find($id);
        if (!$adminUser) {
            throw new \Exception('用户不存在');
        }

        $this->model->deleteById($id);
    }
}
