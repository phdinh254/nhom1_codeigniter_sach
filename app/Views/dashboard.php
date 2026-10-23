<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">Thông tin tài khoản đang đăng nhập</h1>
        <table class="table table-borderless mb-3 w-auto">
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
                        <?= esc($role === 'user' ? 'Độc giả' : $role) ?>
                    </span>
                </td>
            </tr>
        </table>
        <a href="<?= site_url('books') ?>" class="btn btn-primary">
            <i class="bi bi-list-ul me-1"></i> Xem danh sách sách
        </a>
    </div>
</div>

<?= $this->endSection() ?>
