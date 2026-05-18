<?php

require_once "config.php";

class Database {
    private string $username;
    private string $password;
    private string $host;
    private string $database;
    private ?PDO $connection = null;

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    public function connect(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        try {
            $this->connection = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database;sslmode=prefer",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $this->connection;
        }
        catch(PDOException $e) {
            // change to error page e.g. 404 not found etc.
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }
}
