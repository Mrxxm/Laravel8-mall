<?php


namespace App\Services\Impl;


use App\Models\SpecsModel;
use App\Services\SpecsService;

class SpecsServiceImpl implements SpecsService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new SpecsModel();
    }

    public function search(array $data): array
    {
        $select = ['id', 'name'];

        $keyword = $data['keyword'] ?? '';

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['status', '=', 1];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        return $result;
    }

    public function list(array $data): array
    {
        $select = ['id', 'name', 'status', 'create_time', 'update_time', 'delete_time'];

        $keyword = $data['keyword'] ?? '';

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['delete_date'] = DateFormat($res['delete_time']);
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $specs = $this->model->find($id);
        if (!$specs) {
            throw new \Exception('规格不存在');
        }
        if (!$specs->delete_time != 0) {
            throw new \Exception('规格已删除');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $specs = $this->model->find($id);
        if (!$specs) {
            throw new \Exception('规格不存在');
        }
        if (!$specs->delete_time != 0) {
            throw new \Exception('规格已删除');
        }
        $this->model->deleteById($id);
    }
}
