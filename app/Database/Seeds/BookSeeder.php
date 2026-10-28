<?php

namespace App\Database\Seeds;

use App\Models\BookModel;
use CodeIgniter\Database\Seeder;
use RuntimeException;

class BookSeeder extends Seeder
{
    public function run()
    {
        $model = new BookModel($this->db);
        $books = [
            ['title' => 'Dế Mèn phiêu lưu ký', 'author' => 'Tô Hoài', 'publisher' => 'Kim Đồng', 'published_year' => 2020, 'price' => '65000.00', 'quantity' => 20],
            ['title' => 'Tôi thấy hoa vàng trên cỏ xanh', 'author' => 'Nguyễn Nhật Ánh', 'publisher' => 'Trẻ', 'published_year' => 2019, 'price' => '125000.00', 'quantity' => 15],
            ['title' => 'Lập trình PHP và MySQL', 'author' => 'Nhóm biên soạn', 'publisher' => 'Giáo dục Việt Nam', 'published_year' => 2024, 'price' => '95000.00', 'quantity' => 10],
        ];

        // Demo data only; existing records are not changed on a second run.
        foreach ($books as $book) {
            if ($model->where('title', $book['title'])->where('author', $book['author'])->first() !== null) {
                continue;
            }
            if ($model->insert($book) === false) {
                throw new RuntimeException(implode('; ', $model->errors()));
            }
        }
    }
}
