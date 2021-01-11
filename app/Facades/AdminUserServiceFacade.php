<?php


namespace App\Facades;



use Illuminate\Support\Facades\Facade;

class AdminUserServiceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AdminUS';
    }
//    protected static function getFacadeAccessor()
//    {
//        return 'App\Services\Impl\AdminUserServiceImpl';
//    }
}