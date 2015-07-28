<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/27
 * Time: 20:49
 */

namespace Weann\Socialite\Contracts;

interface Factory
{
    /**
     * Get a driver instance.
     *
     * @param string|null $driver
     * @return mixed
     */
    public function driver($driver = null);
}