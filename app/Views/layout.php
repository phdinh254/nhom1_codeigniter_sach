<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Nhà sách') ?> - Nhà sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .pagination { display: flex; gap: .25rem; list-style: none; padding: 0; margin: 0; }
        .pagination a { display: block; padding: .375rem .75rem; border: 1px solid #dee2e6; border-radius: .375rem; background: #fff; text-decoration: none; color: #0d6efd; }
        .pagination .active a { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('books') ?>">
            <i class="bi bi-book-half me-1"></i> Nhà sách
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= site_url('dashboard') ?>" class="text-white text-decoration-none d-none d-sm-inline">
                <i class="bi bi-person-circle me-1"></i><?= esc(session()->get('full_name') ?? '') ?>
                <span class="badge <?= strtolower((string) session()->get('role')) === 'admin' ? 'text-bg-danger' : 'text-bg-secondary' ?>">
                    <?= esc(session()->get('role') === 'user' ? 'Độc giả' : (session()->get('role') ?? '')) ?>
                </span>
            </a>
            <form action="<?= site_url('logout') ?>" method="post" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endif ?>

    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
