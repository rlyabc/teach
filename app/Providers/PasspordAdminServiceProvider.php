<?php

namespace App\Providers;

use App\Foundation\Repository\AdminUserPassportRepository;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\ScopeRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use Laravel\Passport\PassportServiceProvider as BasePassportServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\ResourceServer;

class PasspordAdminServiceProvider extends BasePassportServiceProvider
{
    /**
     * Create and configure a Password grant instance.
     *
     * @return PasswordGrant
     */
    protected function makePasswordGrant()
    {
        $grant = new PasswordGrant(
        //主要是这里，我们调用我们自己UserRepository
            $this->app->make(AdminUserPassportRepository::class),
            $this->app->make(\Laravel\Passport\Bridge\RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    /**
     * Make the authorization service instance.
     *
     * @return \League\OAuth2\Server\AuthorizationServer
     */
//    public function makeAuthorizationServer()
//    {
//        $private_key=get_passport_private_key();
//        return new AuthorizationServer(
//            $this->app->make(ClientRepository::class),
//            $this->app->make(AccessTokenRepository::class),
//            $this->app->make(ScopeRepository::class),
//            $private_key,
//            app('encrypter')->getKey()
//        );
//    }


    /**
     * Register the resource server.
     *
     * @return void
     */
//    protected function registerResourceServer()
//    {
//        $public_key=get_passport_public_key();
//        $this->app->singleton(ResourceServer::class, function () use($public_key) {
//            return new ResourceServer(
//                $this->app->make(AccessTokenRepository::class),
//                $public_key
//            );
//        });
//    }

}
