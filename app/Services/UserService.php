<?php


namespace App\Services;


interface UserService
{
    public function getUserById(int $uId) : array ;

    public function updateUserById(int $uId, array $data) : void;
}
