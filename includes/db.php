<?php

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}


$pdo = Database::getInstance()->getConnection();