<?php


namespace App\Services;


interface CartService
{
    /**
     * 前台
     */
    public function list(array $data) : array ;

    public function add(array $fields) : bool;

    public function update(int $id, array $fields) : bool;

    public function delete(int $id) : bool;
}
