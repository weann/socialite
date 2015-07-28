<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/28
 * Time: 21:21
 */

namespace Weann\Socialite\Two;


class QqProvider extends AbstractProvider
{

    /**
     * Open ID
     *
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
        return $this->buildAuthUrlFromBase('https://graph.qq.com/oauth2.0/authorize', $state);
    }

    /**
     * 获取 access token 的地址。
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://graph.qq.com/oauth2.0/token';
    }

    /**
     * 通过 access token 获取原始用户。
     *
     * @param string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $this->openId = $this->getOpenId($token);
        $userUrl = 'https://graph.qq.com/user/get_user_info?access_token=' . $token . '&oauth_consumer_key=' . $this->clientId . '&openid=' . $this->openId;
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
            'id' => $this->openId, 'nickname' => $user['nickname'], 'name' => '',
            'email' => '', 'avatar' => $user['figureurl_qq_2'] ?: $user['figureurl_qq_1'],
        ]);
    }

    /**
     * 获取 access token 所需的参数。
     *
     * @param string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        $fields = parent::getTokenFields($code);
        $fields['grant_type'] = 'authorization_code';
        return $fields;
    }

    /**
     * 获取 openid
     *
     * @param string $token
     * @return string
     */
    protected function getOpenId($token)
    {
        $openIdUrl = "https://graph.qq.com/oauth2.0/me?access_token=" . $token;
        $response = $this->getHttpClient()->get($openIdUrl);
        $body = $response->getBody();
        $start = strpos($body, '(');
        $length = strrpos($body, ')');
        $body = trim(substr($body, $start + 1, $length - $start - 1));
        return json_decode($body, true)['openid'];
    }

    /**
     * 从响应内容解析出 access token。
     *
     * @param string $body
     * @return string
     */
    protected function parseAccessToken($body)
    {
        parse_str($body, $temp);
        return $temp['access_token'];
    }
}