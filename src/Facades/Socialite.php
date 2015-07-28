<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/28
 * Time: 15:35
 */

namespace Weann\Socialite\Facades;


use Illuminate\Support\Facades\Facade;

class Socialite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'Weann\Socialite\Contracts\Factory';
    }

}