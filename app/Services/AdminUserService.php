<?php


namespace App\Services;


interface AdminUserService
{
    public function list(string $keyword) : array ;

    public function add(array $fields) : void;

    public function update(int $uId, array $fields) : void;

    public function delete(int $uId) : void;
}
