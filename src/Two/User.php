<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/28
 * Time: 10:50
 */

namespace Weann\Socialite\Two;


use Weann\Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * access token
     *
     * @var string
     */
    public $token;

    /**
     * 设置 access token。
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}