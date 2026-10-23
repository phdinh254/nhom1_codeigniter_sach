<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h1 class="h4 mb-3">Sửa sách</h1>

<?= view('books/_form', [
    'action'      => site_url('books/update/' . $book['id']),
    'submitLabel' => 'Cập nhật',
    'submitClass' => 'btn-warning',
    'book'        => $book,
]) ?>

<?= $this->endSection() ?>
