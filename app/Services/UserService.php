<?php


namespace App\Services;


interface UserService
{
    /*
     * 前台
     */
    public function getById(int $uId) : array ;

    public function updateById(int $uId, array $fields) : void;

    /*
     * 后台
     */
    public function list(array $data) : array ;
}
