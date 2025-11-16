<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use PDO;

trait CreatesApplication
{
    protected static bool $mysqlTestDatabasePurged = false;

    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->ensureTestDatabaseDriver($app);

        return $app;
    }

    /**
     * Fall back to a MySQL connection if SQLite is unavailable on the host.
     */
    protected function ensureTestDatabaseDriver($app): void
    {
        $default = $app['config']->get('database.default');
        $explicit = env('TESTS_DB_CONNECTION');

        if ($explicit && $explicit !== 'mysql') {
            $app['config']->set('database.default', $explicit);
            return;
        }

        if ($default === 'sqlite' && !$explicit) {
            if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
                return;
            }
        }

        $this->configureMysqlFallback($app);
    }

    protected function configureMysqlFallback($app): void
    {
        $settings = [
            'driver' => 'mysql',
            'host' => env('TESTS_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TESTS_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('TESTS_DB_DATABASE', 'asccit_consultation_test'),
            'username' => env('TESTS_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('TESTS_DB_PASSWORD', env('DB_PASSWORD', '')),
        ];

        $existing = $app['config']->get('database.connections.mysql', []);

        $app['config']->set('database.connections.mysql', array_merge($existing, $settings));
        $app['config']->set('database.default', 'mysql');

        $this->ensureMysqlDatabaseExists($settings);
        $this->maybeResetMysqlDatabase($app, $settings);
    }

    protected function ensureMysqlDatabaseExists(array $settings): void
    {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;', $settings['host'], $settings['port']);
            $pdo = new PDO($dsn, $settings['username'], $settings['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                str_replace('`', '``', $settings['database'])
            ));
        } catch (\Throwable $e) {
            fwrite(STDERR, PHP_EOL.'[tests] Unable to prepare MySQL fallback database: '.$e->getMessage().PHP_EOL);
        }
    }

    protected function maybeResetMysqlDatabase($app, array $settings): void
    {
        if (self::$mysqlTestDatabasePurged) {
            return;
        }

        $shouldReset = filter_var(
            env('TESTS_DB_RESET', true),
            FILTER_VALIDATE_BOOLEAN,
            ['flags' => FILTER_NULL_ON_FAILURE]
        );

        if ($shouldReset === false) {
            return;
        }

        $this->purgeMysqlTestDatabase($app, $settings);
        self::$mysqlTestDatabasePurged = true;
    }

    protected function purgeMysqlTestDatabase($app, array $settings): void
    {
        try {
            $connection = $app['db']->connection('mysql');
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');
            $tables = $connection->select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0] ?? null;

                if (!$tableName) {
                    continue;
                }

                $connection->statement(sprintf('DROP TABLE IF EXISTS `%s`', str_replace('`', '``', $tableName)));
            }

            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            fwrite(STDERR, PHP_EOL.'[tests] Unable to reset MySQL test database: '.$e->getMessage().PHP_EOL);
        }
    }
}
