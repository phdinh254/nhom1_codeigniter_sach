<?php

namespace App\Controllers;

use App\Models\BookModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CRUD controller for books.
 *
 * Views (books/index, books/create, books/edit) are built by the frontend task;
 * field rules and validation messages live in BookModel.
 */
class Book extends BaseController
{
    protected $helpers = ['form', 'url'];

    protected BookModel $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookModel();
    }

    /**
     * GET /books
     */
    public function index(): string
    {
        $keyword = $this->request->getGet('keyword');
        $keyword = is_string($keyword) ? trim($keyword) : '';

        if ($keyword !== '') {
            $this->bookModel->like('title', $keyword);
        }

        return view('books/index', [
            'title'   => 'Danh sách sách',
            'books'   => $this->bookModel->orderBy('id', 'DESC')->paginate(10),
            'pager'   => $this->bookModel->pager,
            'keyword' => $keyword,
        ]);
    }

    /**
     * GET /books/create
     */
    public function create(): string
    {
        return view('books/create', [
            'title' => 'Thêm sách',
        ]);
    }

    /**
     * POST /books/store
     */
    public function store(): RedirectResponse
    {
        if (! $this->bookModel->insert($this->bookData())) {
            return redirect()->back()->withInput()->with('errors', $this->bookModel->errors());
        }

        return redirect()->to('/books')->with('success', 'Thêm sách thành công.');
    }

    /**
     * GET /books/edit/{id}
     */
    public function edit(int $id): string
    {
        return view('books/edit', [
            'title' => 'Sửa sách',
            'book'  => $this->findOrFail($id),
        ]);
    }

    /**
     * POST /books/update/{id}
     */
    public function update(int $id): RedirectResponse
    {
        $this->findOrFail($id);

        if (! $this->bookModel->update($id, $this->bookData())) {
            return redirect()->back()->withInput()->with('errors', $this->bookModel->errors());
        }

        return redirect()->to('/books')->with('success', 'Cập nhật sách thành công.');
    }

    /**
     * POST /books/delete/{id}
     */
    public function delete(int $id): RedirectResponse
    {
        $this->findOrFail($id);

        if (! $this->bookModel->delete($id)) {
            return redirect()->to('/books')->with('error', 'Xóa sách thất bại.');
        }

        return redirect()->to('/books')->with('success', 'Xóa sách thành công.');
    }

    /**
     * Posted form data; BookModel drops any field not in its $allowedFields.
     * Empty optional inputs become NULL: MySQL strict mode rejects '' for
     * the SMALLINT published_year column.
     */
    private function bookData(): array
    {
        $data = array_intersect_key(
            $this->request->getPost() ?? [],
            array_flip(['title', 'author', 'publisher', 'published_year', 'price', 'quantity', 'description']),
        );

        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $data[$field] = trim($value);
            }
        }

        foreach (['publisher', 'published_year', 'description'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function findOrFail(int $id): array|object
    {
        $book = $this->bookModel->find($id);

        if ($book === null) {
            throw PageNotFoundException::forPageNotFound('Không tìm thấy sách có ID ' . $id);
        }

        return $book;
    }
}
