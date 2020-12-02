<?php


namespace App\Services;


interface UserService
{
    public function getById(int $uId) : array ;

    public function updateById(int $uId, array $fields) : void;

    public function list(array $data) : array ;
}
