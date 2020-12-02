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
        // TODO: Implement add() method.
    }

    public function update(int $id, array $fields): void
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): void
    {
        // TODO: Implement delete() method.
    }
}
