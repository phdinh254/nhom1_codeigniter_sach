<?php

namespace App\Validation;

use CodeIgniter\I18n\Time;

/** Server rules also apply when callers bypass the HTML form. */
class LibraryRules
{
    public static function currentYear(): int
    {
        return (int) Time::now('Asia/Ho_Chi_Minh')->format('Y');
    }

    public function publication_year($value, ?string &$error = null): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $year = self::currentYear();
        $error = 'Năm xuất bản phải là số nguyên từ 1000 đến ' . $year . '.';

        return (is_int($value) || is_string($value))
            && preg_match('/\A[0-9]{4}\z/', (string) $value) === 1
            && (int) $value >= 1000
            && (int) $value <= $year;
    }

    public function library_text($value, string $max, array $data = [], ?string &$error = null): bool
    {
        $error = 'Trường này phải có nội dung, không chứa ký tự điều khiển và tối đa ' . $max . ' ký tự.';

        return is_string($value)
            && preg_match('/(*UCP)\S/u', $value) === 1
            && preg_match('/\A[^\x00-\x1F\x7F]+\z/u', $value) === 1
            && mb_strlen($value, 'UTF-8') <= (int) $max;
    }

    public function optional_library_text($value, string $max, array $data = [], ?string &$error = null): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return $this->library_text($value, $max, $data, $error);
    }

    public function library_price($value, ?string &$error = null): bool
    {
        $error = 'Giá phải từ 0 đến 99999999.99, dùng dấu chấm và tối đa 2 chữ số thập phân.';

        return (is_int($value) || is_string($value))
            && preg_match('/\A[0-9]{1,8}(?:\.[0-9]{1,2})?\z/', (string) $value) === 1;
    }

    public function library_quantity($value, ?string &$error = null): bool
    {
        $error = 'Số lượng phải là số nguyên từ 0 đến 4294967295.';

        return (is_int($value) || is_string($value))
            && preg_match('/\A[0-9]{1,10}\z/', (string) $value) === 1
            && (float) $value <= 4294967295;
    }

    public function library_description($value, ?string &$error = null): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $error = 'Mô tả tối đa 10000 ký tự và không được chứa ký tự điều khiển không hợp lệ.';

        return is_string($value)
            && preg_match('/\A[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F]*\z/u', $value) === 1
            && mb_strlen($value, 'UTF-8') <= 10000;
    }

    public function library_status($value, ?string &$error = null): bool
    {
        $error = 'Trạng thái chỉ nhận 0 hoặc 1.';

        return in_array($value, [0, 1, '0', '1'], true);
    }
}
