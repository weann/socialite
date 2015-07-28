<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/27
 * Time: 19:56
 */

namespace Weann\Socialite;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use Weann\Socialite\Contracts\Factory;

class SocialiteManager extends Manager implements Factory
{
    /**
     * 构建提供者。
     *
     * @param string $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * 构建微信登录。
     *
     * @return \Weann\Socialite\Two\AbstractProvider
     */
    protected function createWechatDriver()
    {
        $config = $this->app['config']['services.wechat'];
        return $this->buildProvider('Weann\Socialite\Two\WechatProvider', $config);
    }

    /**
     * 构建 QQ 登录。
     *
     * @return \Weann\Socialite\Two\AbstractProvider
     */
    protected function createQqDriver()
    {
        $config = $this->app['config']['services.qq'];
        return $this->buildProvider('Weann\Socialite\Two\QqProvider', $config);
    }

    /**
     * 实例化提供者。
     *
     * @param string $provider
     * @param array $config
     * @return \Weann\Socialite\Two\AbstractProvider
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'], $config['client_id'],
            $config['client_secret'], $config['redirect']
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException("No Socialite driver was specified.");
    }
}