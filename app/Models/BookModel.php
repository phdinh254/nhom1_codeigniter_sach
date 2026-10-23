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
        'title' => 'library_text[255]',
        'author' => 'library_text[150]',
        'publisher' => 'optional_library_text[150]',
        'published_year' => 'publication_year',
        'price' => 'library_price',
        'quantity' => 'library_quantity',
        'description' => 'library_description',
    ];
    protected $validationMessages = [
        'title' => ['library_text' => 'Tên sách không được trống, chỉ có khoảng trắng, chứa ký tự điều khiển hoặc dài quá 255 ký tự.'],
        'author' => ['library_text' => 'Tác giả không được trống, chỉ có khoảng trắng, chứa ký tự điều khiển hoặc dài quá 150 ký tự.'],
        'publisher' => ['optional_library_text' => 'Nhà xuất bản tối đa 150 ký tự và không chứa ký tự điều khiển.'],
    ];
    protected $beforeInsert = ['normalizeBookData'];
    protected $beforeUpdate = ['normalizeBookData'];

    /** Validation runs first; normalize only valid strings, never coerce arrays. */
    protected function normalizeBookData(array $data): array
    {
        foreach (['title', 'author', 'publisher', 'published_year', 'price', 'quantity', 'description'] as $field) {
            if (isset($data['data'][$field]) && is_string($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
            }
        }
        foreach (['publisher', 'published_year', 'description'] as $field) {
            if (array_key_exists($field, $data['data']) && $data['data'][$field] === '') {
                $data['data'][$field] = null;
            }
        }

        return $data;
    }
}
