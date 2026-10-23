<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<?php $isAdmin = strtolower((string) session()->get('role')) === 'admin'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Danh sách sách</h1>
    <?php if ($isAdmin): ?>
        <a href="<?= site_url('books/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Thêm sách
        </a>
    <?php endif ?>
</div>

<form action="<?= site_url('books') ?>" method="get" class="row g-2 mb-3">
    <div class="col-sm-8 col-md-6">
        <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên sách..."
               value="<?= esc($keyword ?? '') ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Tìm</button>
        <?php if (! empty($keyword)): ?>
            <a href="<?= site_url('books') ?>" class="btn btn-outline-secondary">Xóa lọc</a>
        <?php endif ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle bg-white">
        <thead class="table-primary">
            <tr>
                <th style="width:60px">ID</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Nhà xuất bản</th>
                <th>Năm</th>
                <th class="text-end">Giá</th>
                <th class="text-end">SL</th>
                <?php if ($isAdmin): ?><th style="width:150px">Thao tác</th><?php endif ?>
            </tr>
        </thead>
        <tbody>
        <?php if (! empty($books)): ?>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= esc($book['id']) ?></td>
                    <td><?= esc($book['title']) ?></td>
                    <td><?= esc($book['author']) ?></td>
                    <td><?= esc($book['publisher'] ?? '') ?></td>
                    <td><?= esc($book['published_year'] ?? '') ?></td>
                    <td class="text-end"><?= number_format((float) $book['price'], 0, ',', '.') ?> đ</td>
                    <td class="text-end"><?= esc($book['quantity']) ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <a href="<?= site_url('books/edit/' . $book['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> Sửa
                            </a>
                            <form action="<?= site_url('books/delete/' . $book['id']) ?>" method="post" class="d-inline"
                                  onsubmit="return confirm('Bạn chắc chắn muốn xóa sách này?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    <?php endif ?>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= $isAdmin ? 8 : 7 ?>" class="text-center text-muted py-4">Chưa có sách nào.</td>
            </tr>
        <?php endif ?>
        </tbody>
    </table>
</div>

<?php if (isset($pager)): ?>
    <div class="d-flex justify-content-center"><?= $pager->links() ?></div>
<?php endif ?>

<?= $this->endSection() ?>
