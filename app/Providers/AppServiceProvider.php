<?php

namespace App\Providers;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\IntegerType;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Connection;
use Illuminate\Database\SQLiteConnection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class ($connection, $database, $prefix, $config) extends SQLiteConnection {
                public function statement($query, $bindings = [])
                {
                    if (is_string($query) && stripos($query, 'FOREIGN_KEY_CHECKS') !== false) {
                        $query = stripos($query, '= 0') !== false
                            ? 'PRAGMA foreign_keys = OFF'
                            : 'PRAGMA foreign_keys = ON';
                    }
                    return parent::statement($query, $bindings);
                }
            };
        });
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
