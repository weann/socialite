<?php
/**
 * Created by PhpStorm.
 * User: zhangjicheng
 * Date: 15/7/27
 * Time: 20:58
 */

namespace Weann\Socialite\Two;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractProvider
{
    /**
     * Request 实例
     *
     * @var Request
     */
    protected $request;

    /**
     * 唯一标识
     *
     * @var string
     */
    protected $clientId;

    /**
     * 密钥
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * 回调地址
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * 授权作用域
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * 授权作用域分隔符
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * 表示需要使用 state 参数。
     *
     * @var bool
     */
    protected $stateless = false;

    /**
     * 创建提供者实例。
     *
     * @param Request $request
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl)
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * 获取授权地址。
     *
     * @param string $state
     * @return string
     */
    abstract protected function getAuthUrl($state);

    /**
     * 获取 access token 的地址。
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * 通过 access token 获取原始用户。
     *
     * @param string $token
     * @return array
     */
    abstract protected function getUserByToken($token);

    /**
     * 将原始用户数组映射为用户对象。
     *
     * @param array $user
     * @return \Weann\Socialite\Two\User
     */
    abstract protected function mapUserToObject(array $user);

    /**
     * 将用户重定向到提供者的授权地址。
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $state = null;

        if ($this->usesState()) {
            $this->request->getSession()->set('state', $state = Str::random(40));
        }

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * 构建授权地址。
     *
     * @param string $url
     * @param string $state
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url . '?' . http_build_query($this->getCodeFields($state));
    }

    /**
     * 获取构建授权地址所需的参数。
     *
     * @param string|null $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }

    /**
     * 格式化授权作用域数组。
     *
     * @param array $scopes
     * @param string $scopeSeparator
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * 获取经过授权后的用户实例。
     *
     * @return $this
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessToken($this->getCode())
        ));

        return $user->setToken($token);
    }

    /**
     * 检查 state 参数是否无效。
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }

        $session = $this->request->getSession();

        return !($this->request->input('state') === $session->get('state'));
    }

    /**
     * 通过 code 获取 access token。
     *
     * @param string $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
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
            'client_id' => $this->clientId, 'client_secret' => $this->clientSecret,
            'code' => $code, 'redirect_uri' => $this->redirectUrl
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
        return json_decode($body, true)['access_token'];
    }

    /**
     * 获取 code 参数。
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->input('code');
    }

    /**
     * 设置授权作用域。
     *
     * @param array $scopes
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * 获取 HTTP Client 实例。
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new Client;
    }

    /**
     * 是否使用了 state 参数。
     *
     * @return bool
     */
    protected function usesState()
    {
        return !$this->stateless;
    }

    /**
     * 是否没有使用 state 参数。
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }
}