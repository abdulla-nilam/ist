<?php
namespace App\Services;

use App\Database\Connection;

class UserService
{
    // Register a new user
    public static function register(string $username, string $password): bool
    {
        $pdo = Connection::getInstance();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        try {
            return $stmt->execute([$username, $hashedPassword]);
        } catch (\PDOException $e) {
            // duplicate
            return false;
        }
    }

    public static function login(string $username, string $password): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        if (password_verify($password, $row['password'])) {
            // Update last_logged_in to current datetime.
            $updateStmt = $pdo->prepare("UPDATE users SET last_logged_in = datetime('now') WHERE username = ?");
            $updateStmt->execute([$username]);
            return true;
        }
        return false;
    }
}
