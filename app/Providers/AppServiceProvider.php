<?php

namespace App\Providers;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\IntegerType;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if (app()->runningInConsole()) {
            try {
                // Register tinyinteger type for Doctrine
                if (!Type::hasType('tinyinteger')) {
                    Type::addType('tinyinteger', IntegerType::class);
                }

                $platform = DB::getDoctrineConnection()->getDatabasePlatform();
                $platform->registerDoctrineTypeMapping('tinyint', 'tinyinteger');
            } catch (\Exception $e) {
                Log::error('Error when registering tinyinteger type: ' . $e->getMessage());
            }
        }
    }
}
