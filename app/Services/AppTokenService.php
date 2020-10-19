<?php


namespace App\Services;


interface AppTokenService
{
    public function get(string $username, string $password) : string;
}
