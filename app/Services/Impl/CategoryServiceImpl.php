<?php


namespace App\Services\Impl;


use App\Models\CategoryModel;
use App\Services\CategoryService;
use App\Utils\ArrayUtil;

class CategoryServiceImpl implements CategoryService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new CategoryModel();
    }

    public function listAll(): array
    {
        $select = ['id as category_id', 'name', 'pid'];

        $conditions = [];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['delete_time', '=', 0];

        $orderBy = array('sort', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        if (count($result)) {
            $result = ArrayUtil::getTree($result);
            $result = ArrayUtil::sliceTreeArr($result);
        }

        return $result;
    }

    public function search(array $data): array
    {
        $select = ['id', 'name', 'path'];

        $pid = $data['pid'];

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['pid', '=', $pid];

        $orderBy = array('sort', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        return $result;
    }

    public function list(array $data): array
    {
        $select = ['id', 'name', 'pid', 'icon', 'path', 'status', 'sort', 'create_time', 'update_time', 'delete_time'];

        $pid = $data['pid'];
        $keyword = $data['keyword'] ?? '';

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['pid', '=', $pid];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('sort', 'asc');

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
        $category = $this->model->add($fields);
        $id = $category->id;
        // 处理path
        $upd['path'] = $id;
        if ($fields['pid'] != 0) {
            $parentCategory = CategoryModel::find($fields['pid']);
            $upd['path'] = $parentCategory->path . ",{$upd['path']}";
        }
        $this->model->updateById($id, $upd);
    }

    public function update(int $id, array $fields): void
    {
        $category = $this->model->find($id);
        if (!$category) {
            throw new \Exception('分类不存在');
        }
        if ($category->delete_time != 0) {
            throw new \Exception('分类已删除');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $category = $this->model->find($id);
        if (!$category) {
            throw new \Exception('分类不存在');
        }
        if ($category->delete_time != 0) {
            throw new \Exception('分类已删除');
        }
        $this->model->deleteById($id);
    }
}
