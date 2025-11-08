<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class CreateDatabase extends Command
{
    protected $signature = 'db:create {--connection=}';

    protected $description = 'Create the configured database if it does not already exist (PostgreSQL only).';

    public function handle(): int
    {
        $connectionName = $this->option('connection') ?? config('database.default');
        $config = config("database.connections.$connectionName");

        if (! $config) {
            $this->error("Connection [$connectionName] is not defined in config/database.php.");
            return static::FAILURE;
        }

        if (($config['driver'] ?? null) !== 'pgsql') {
            $this->error('db:create currently only supports the pgsql driver.');
            return static::FAILURE;
        }

        $database = $config['database'] ?? null;
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '5432';
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        if (! $database) {
            $this->error('No database name configured.');
            return static::FAILURE;
        }

        try {
            $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $exception) {
            $this->error('Unable to connect to PostgreSQL: ' . $exception->getMessage());
            return static::FAILURE;
        }

        $statement = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
        $statement->execute(['database' => $database]);

        if ($statement->fetchColumn()) {
            $this->info("Database [$database] already exists.");
            return static::SUCCESS;
        }

        try {
            $pdo->exec("CREATE DATABASE \"$database\"");
            $this->info("Database [$database] created successfully.");
            return static::SUCCESS;
        } catch (PDOException $exception) {
            $this->error('Failed to create database: ' . $exception->getMessage());
            return static::FAILURE;
        }
    }
}
