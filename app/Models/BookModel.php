<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title', 'author', 'publisher', 'published_year',
        'price', 'quantity', 'description',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'author' => 'required|max_length[150]',
        'publisher' => 'permit_empty|max_length[150]',
        'published_year' => 'permit_empty|integer|greater_than_equal_to[1000]|less_than_equal_to[2100]',
        'price' => 'required|regex_match[/^\d{1,8}(\.\d{1,2})?$/]',
        'quantity' => 'required|is_natural|less_than_equal_to[4294967295]',
        'description' => 'permit_empty|max_length[10000]',
    ];
}
