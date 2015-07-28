# Laravel Socialite
Laravel 第三方登录，支持微信、QQ。

## 安装
使用 composer 命令
```shell
composer require weann/socialite
```

注册服务提供者
```php
Weann\Socialite\SocialiteServiceProvider::class,
```

注册 `Facade`
```php
'Socialite' => Weann\Socialite\Facades\Socialite::class,
```

## 使用
将用户重定向到授权页面。
```php
Route::get('/', function () {
    return Socialite::driver('wechat')->redirect();
});
```

授权后的回调。
```php
Route::get('callback', function () {
    $user = Socialite::driver('wechat')->user()
    dd($user);
});
```
