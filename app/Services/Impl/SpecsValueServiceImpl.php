<?php


namespace App\Services\Impl;


use App\Models\SpecsModel;
use App\Models\SpecsValueModel;
use App\Services\SpecsService;

class SpecsValueServiceImpl implements SpecsService
{
    public $model = null;
    protected $specsService = null;

    public function __construct()
    {
        $this->model = new SpecsValueModel();
        $this->specsService = new SpecsServiceImpl();
    }

    public function search(array $data): array
    {
        $select = ['id', 'name'];

        $keyword = $data['keyword'] ?? '';
        $specsId = $data['specs_id'];

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['specs_id', '=', $specsId];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        return $result;
    }

    public function list(array $data): array
    {
        $select = ['id', 'specs_id', 'name', 'status', 'create_time', 'update_time', 'delete_time'];

        $keyword = $data['keyword'] ?? '';
        $specsId = $data['specs_id'];

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['specs_id', '=', $specsId];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['specs_name'] = ($this->specsService->model->find($specsId))->name;
                $res['delete_date'] = DateFormat($res['delete_time']);
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        $specsId = $fields['specs_id'];
        $specs = $this->specsService->model->where('delete_time', 0)->find($specsId);
        if (!$specs) {
            throw new \Exception('规格不存在');
        }
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $specsValue = $this->model->find($id);
        if (!$specsValue) {
            throw new \Exception('规格属性不存在');
        }
        if ($specsValue->delete_time != 0) {
            throw new \Exception('规格属性已删除');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $specsValue = $this->model->find($id);
        if (!$specsValue) {
            throw new \Exception('规格属性不存在');
        }
        if ($specsValue->delete_time != 0) {
            throw new \Exception('规格属性已删除');
        }
        $this->model->deleteById($id);
    }
}
