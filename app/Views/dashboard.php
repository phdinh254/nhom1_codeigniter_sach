<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trang quản lý - Nhà sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('dashboard') ?>">
            <i class="bi bi-book-half me-1"></i> Nhà sách
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white d-none d-sm-inline">
                <i class="bi bi-person-circle me-1"></i><?= esc($fullName) ?>
            </span>
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
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endif ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Thông tin tài khoản đang đăng nhập</h1>
            <table class="table table-borderless mb-0 w-auto">
                <tr>
                    <th class="ps-0 pe-4">Họ tên</th>
                    <td><?= esc($fullName) ?></td>
                </tr>
                <tr>
                    <th class="ps-0 pe-4">Tên đăng nhập</th>
                    <td><?= esc($username) ?></td>
                </tr>
                <tr>
                    <th class="ps-0 pe-4">Vai trò</th>
                    <td>
                        <span class="badge <?= $role === 'admin' ? 'text-bg-danger' : 'text-bg-secondary' ?>">
                            <?= esc($role) ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
