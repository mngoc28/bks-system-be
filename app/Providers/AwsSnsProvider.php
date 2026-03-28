<?php

namespace App\Providers;

use Aws\Sns\SnsClient;
use Illuminate\Support\ServiceProvider;

class AwsSnsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(SnsClient::class, function () {
            $params = config("aws.sns.params");
            return new SnsClient($params);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
