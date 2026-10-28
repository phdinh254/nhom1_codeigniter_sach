<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h1 class="h4 mb-3">Thêm sách</h1>

<?= view('books/_form', [
    'action'      => site_url('books/store'),
    'submitLabel' => 'Lưu',
    'submitClass' => 'btn-primary',
]) ?>

<?= $this->endSection() ?>
