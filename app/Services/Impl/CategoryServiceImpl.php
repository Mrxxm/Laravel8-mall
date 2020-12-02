<?php


namespace App\Services\Impl;


use App\Models\CategoryModel;
use App\Services\CategoryService;

class CategoryServiceImpl implements CategoryService
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new CategoryModel();
    }

    public function list(array $data): array
    {
        // TODO: Implement list() method.
    }

    public function add(array $fields): void
    {
        // 处理path
        if ($fields['pid'] != 0) {
            $category = CategoryModel::find($fields['pid']);
            $fields['path'] = $category->path . ",{$fields['pid']}";
        } else {
            $fields['path'] = $fields['pid'];
        }
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $category = $this->model->find($id);
        if (!$category) {
            throw new \Exception('分类不存在');
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
        $this->model->deleteById($id);
    }
}
