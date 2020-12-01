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
        $select = ['id', 'app_id', 'app_secret', 'app_description', 'scope', 'scope_description', 'create_time', 'update_time'];

        $keyword = $conditions['keyword'] ?? '';
        $conditions = [];
        $conditions[] = ['delete_time', '!=', 0];
        if (!empty($keyword)) {
            $conditions[] = ['app_id', 'like', "%{$keyword}%"];
        }

        return $this->model->list($select, $conditions);
    }

    public function add(array $fields): void
    {
        return $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        unset($fields['id']);
        return $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        return $this->model->deleteById($id);
    }
}
