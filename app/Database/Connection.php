<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static PDO|null $pdo = null;

    /**
     * Returns the PDO instance for the SQLite database.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $dbPath = $config['sqlite_path'] ?? __DIR__ . '/../../database.sqlite';

            try {
                self::$pdo = new PDO('sqlite:' . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::initialize();
            } catch (PDOException $e) {
                die('Database connection error: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * Initialize the database
     *
     * @return void
     */
    private static function initialize(): void
    {
        $pdo = self::$pdo;
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                last_logged_in DATETIME
            )"
        ];

        foreach ($tables as $sql) {
            $pdo->exec($sql);
        }
    }
}
