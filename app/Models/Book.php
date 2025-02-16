<?php
namespace App\Models;

class Book
{
    public int $id;
    public string $title;
    public string $author;
    public int $published_year;

    public function __construct(int $id, string $title, string $author, int $published_year)
    {
        $this->id             = $id;
        $this->title          = $title;
        $this->author         = $author;
        $this->published_year = $published_year;
    }
}
