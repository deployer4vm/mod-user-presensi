<?php
namespace hpsynapse\moduser\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\AliasLoader;
use hpsynapse\moduser\Facades\UserAuth;
use hpsynapse\moduser\Console\Commands\DefaultSysUser;
use hpsynapse\moduser\Console\Commands\UpdateRoleFromJson;
use hpsynapse\moduser\Console\Commands\UpdateUserGroup;

// use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    // private $config;
    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        $kernel->appendMiddlewareToGroup('web', \hpsynapse\moduser\Middleware\InitAuthWeb::class);
        $kernel->appendMiddlewareToGroup('api', \hpsynapse\moduser\Middleware\ValidateClientKey::class);
        $kernel->appendMiddlewareToGroup('api', \hpsynapse\moduser\Middleware\InitAuthAPI::class);
        $this->app['router']->aliasMiddleware('auth.useronly', \hpsynapse\moduser\Middleware\APIUserOnly::class);
        $this->app['router']->aliasMiddleware('auth.h2honly', \hpsynapse\moduser\Middleware\APIH2HOnly::class);
        $this->app['router']->aliasMiddleware('auth.webToken', \hpsynapse\moduser\Middleware\WebTokenAuth::class);
        $this->app['router']->aliasMiddleware('auth.ApiWebAuth', \hpsynapse\moduser\Middleware\ApiWebAuth::class);
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateRoleFromJson::class,
                DefaultSysUser::class,
                UpdateUserGroup::class
            ]);
        }
    }
    

    /**
     * Register
     */
    public function register()
    {        
        $this->app->booting(function() {
            $loader = AliasLoader::getInstance();
            $loader->alias('UserAuth', UserAuth::class);
        });
    }

}
