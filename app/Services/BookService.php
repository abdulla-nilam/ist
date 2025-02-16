<?php
namespace App\Services;

use App\Database\Connection;

class BookService
{
    // Retrieve all books from the database
    public static function getAll(): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->query("SELECT * FROM books");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Find a book by id
    public static function find(int $id): array|null
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $book ?: null;
    }

    // Create a new book
    public static function create(string $title, string $author, int $published_year, string $created_by): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("INSERT INTO books (title, author, published_year, created_by, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        $stmt->execute([$title, $author, $published_year, $created_by]);
        $id = $pdo->lastInsertId();
        return self::find((int)$id);
    }

    // Update an existing book
    public static function update(int $id, string $title, string $author, int $published_year, string $updated_by): array|null
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, published_year = ?, updated_by = ?, updated_at = datetime('now') WHERE id = ?");
        $stmt->execute([$title, $author, $published_year, $updated_by, $id]);
        return self::find($id);
    }

    // Delete a book by id
    public static function delete(int $id): bool
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
