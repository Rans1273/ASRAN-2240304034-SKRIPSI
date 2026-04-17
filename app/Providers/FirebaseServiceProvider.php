<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Database::class, function () {

            $databaseUrl = env('FIREBASE_DATABASE_URL');

            if (!$databaseUrl) {
                throw new \Exception("FIREBASE_DATABASE_URL belum di-set di .env");
            }

            return (new Factory)
                ->withServiceAccount(storage_path('app/firebase_credentials.json'))
                ->withDatabaseUri($databaseUrl)
                ->createDatabase();
        });
    }
}