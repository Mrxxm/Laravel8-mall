<?php


namespace App\Services;


interface AdminUserService
{
    public function list(array $data) : array ;

    public function add(array $fields) : void;

    public function update(int $id, array $fields) : void;

    public function delete(int $id) : void;
}
