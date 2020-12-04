<?php


namespace App\Services;


interface CartService
{
    /**
     * 前台
     */
    public function list(array $data) : array ;

    public function add(array $fields) : void;

    public function update(int $id, array $fields) : void;

    public function delete(string $ids) : void;
}
