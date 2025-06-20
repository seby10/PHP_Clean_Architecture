<?php

namespace App\Data\PostgresDB;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class PostgresDatabase
{
    private static ?PostgresDatabase $instance = null;
    private bool $isConnected = false;
    private Capsule $capsule;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function connect(array $options): bool
    {
        if ($this->isConnected) {
            echo "PostgreSQL ya está conectado\n";
            return true;
        }

        try {
            $this->capsule = new Capsule;
            
            $this->capsule->addConnection([
                'driver' => 'pgsql',
                'host' => $options['host'],
                'port' => $options['port'],
                'database' => $options['database'],
                'username' => $options['user'],
                'password' => $options['password'],
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ]);

            $this->capsule->setAsGlobal();
            $this->capsule->bootEloquent();

            // Test connection
            $this->capsule->getConnection()->getPdo();

            $this->isConnected = true;
            // echo "PostgreSQL connected successfully\n";
            
            // Create tables if they don't exist
            $this->createTables();
            
            return true;
        } catch (\Exception $e) {
            echo "Error connecting to PostgreSQL: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function getConnection(): Capsule
    {
        if (!$this->isConnected) {
            throw new \Exception('Database not connected. Call connect() first.');
        }
        return $this->capsule;
    }

    private function createTables(): void
    {
        $schema = $this->capsule->schema();
        
        if (!$schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('img')->nullable();
                $table->json('roles')->default(json_encode(['USER_ROLE']));
                $table->timestamps();
            });
        }
    }
}