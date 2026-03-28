<?php

namespace App\Providers;

use App\Services\CognitoTokenApiGuard;
use Ellaisys\Cognito\AwsCognitoClient;
use Ellaisys\Cognito\Exceptions\InvalidTokenException;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Support\ServiceProvider;

class CognitoAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $loader = AliasLoader::getInstance([InvalidTokenException::class]);
        $loader->alias(
            InvalidTokenException::class,
            \App\Exceptions\Ellaisys\Cognito\InvalidTokenException::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // //
        // Auth::extend("cognito-token-api", function (
        //     Application $app,
        //     $name,
        //     array $config
        // ) {
        //     $guard = new CognitoTokenApiGuard(
        //         $app["ellaisys.aws.cognito"],
        //         ($client = $app->make(AwsCognitoClient::class)),
        //         $app["request"],
        //         Auth::createUserProvider($config["provider"])
        //     );

        //     $guard->setRequest($app->refresh("request", $guard, "setRequest"));

        //     return $guard;
        // });
    }
}
