<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connectors\SqlServerConnector;

class SqlSrvConnectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Override the sqlsrv PDO connector used by Laravel
        $this->app->singleton('db.connector.sqlsrv', function () {
            return new class extends SqlServerConnector {
                public function getOptions(array $config): array
                {
                    $options = parent::getOptions($config);

                    // Remove PDO attributes that trigger IMSSP "invalid attribute" on some pdo_sqlsrv builds
                    unset($options[\PDO::ATTR_EMULATE_PREPARES]);
                    unset($options[\PDO::ATTR_STRINGIFY_FETCHES]);

                    return $options;
                }
            };
        });
    }
}
