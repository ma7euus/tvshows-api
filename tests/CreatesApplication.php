<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use PDO;
use PDOException;

trait CreatesApplication
{
    private static bool $testingDatabaseEnsured = false;

    public function createApplication()
    {
        $this->ensureTestingDatabaseExists();

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function ensureTestingDatabaseExists(): void
    {
        if (self::$testingDatabaseEnsured) {
            return;
        }

        if ((getenv('APP_ENV') ?: '') !== 'testing' || (getenv('DB_CONNECTION') ?: '') !== 'pgsql') {
            return;
        }

        $database = getenv('DB_DATABASE') ?: 'tvshow_test';
        $maintenanceDatabase = getenv('DB_MAINTENANCE_DATABASE') ?: 'postgres';
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $username = getenv('DB_USERNAME') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: 'postgres';

        $pdo = new PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $maintenanceDatabase),
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        );

        $statement = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
        $statement->execute(['database' => $database]);

        if (! $statement->fetchColumn()) {
            try {
                $pdo->exec(sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $database)));
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '42P04') {
                    throw $exception;
                }
            }
        }

        self::$testingDatabaseEnsured = true;
    }
}
