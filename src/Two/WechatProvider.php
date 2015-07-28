<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/27
 * Time: 20:56
 */

namespace Weann\Socialite\Two;


class WechatProvider extends AbstractProvider
{
    /**
     * 授权作用域
     *
     * @var array
     */
    protected $scopes = ['snsapi_login'];

    /**
     * Open ID
     * @var string
     */
    protected $openId;

    /**
     * 获取授权地址。
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://open.weixin.qq.com/connect/qrconnect', $state);
    }

    /**
     * 获取 access token 的地址。
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    /**
     * 通过 access token 获取原始用户。
     *
     * @param string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $token . '&openid=' . $this->openId;
        $response = $this->getHttpClient()->get($userUrl);
        $user = json_decode($response->getBody(), true);
        return $user;
    }

    /**
     * 将原始用户数组映射为用户对象。
     *
     * @param array $user
     * @return \Weann\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['openid'], 'nickname' => $user['nickname'], 'name' => '',
            'email' => '', 'avatar' => $user['headimgurl'],
        ]);
    }

    /**
     * 获取构建授权地址所需的参数。
     *
     * @param string|null $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);
        unset($fields['client_id']);
        $fields['appid'] = $this->clientId;
        return $fields;
    }

    /**
     * 获取 access token 所需的参数。
     *
     * @param string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'appid' => $this->clientId, 'secret' => $this->clientSecret,
            'code' => $code, 'grant_type' => 'authorization_code'
        ];
    }

    /**
     * 从响应内容解析出 access token。
     *
     * @param string $body
     * @return string
     */
    protected function parseAccessToken($body)
    {
        $this->openId = json_decode($body, true)['openid'];
        return parent::parseAccessToken($body);
    }
}