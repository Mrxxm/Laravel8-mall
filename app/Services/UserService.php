<?php


namespace App\Services;


interface UserService
{
    public function updateUserById(int $uId, array $data) : void;
}
