<?php
/**
 * Form dùng chung cho create.php và edit.php
 * Biến cần truyền vào: $action (URL), $submitLabel, $submitClass, $book (mảng, tùy chọn)
 */
$book   = $book ?? [];
$errors = session()->getFlashdata('errors') ?? [];
$val = static function (string $key) use ($book): string {
    $value = old($key, $book[$key] ?? '');

    return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
};
?>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ((array) $errors as $message): ?>
                <li><?= esc($message) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form action="<?= esc($action) ?>" method="post" class="bg-white p-4 rounded border">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="title" class="form-label">Tên sách <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control" maxlength="255"
               value="<?= esc($val('title')) ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="author" class="form-label">Tác giả <span class="text-danger">*</span></label>
            <input type="text" id="author" name="author" class="form-control" maxlength="150"
                   value="<?= esc($val('author')) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="publisher" class="form-label">Nhà xuất bản</label>
            <input type="text" id="publisher" name="publisher" class="form-control" maxlength="150"
                   value="<?= esc($val('publisher')) ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="published_year" class="form-label">Năm xuất bản</label>
            <input type="number" id="published_year" name="published_year" class="form-control"
                   min="1000" max="<?= \App\Validation\LibraryRules::currentYear() ?>" step="1" value="<?= esc($val('published_year')) ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label for="price" class="form-label">Giá (đ) <span class="text-danger">*</span></label>
            <input type="number" id="price" name="price" class="form-control"
                   min="0" max="99999999.99" step="0.01" value="<?= esc($val('price')) ?>" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="quantity" class="form-label">Số lượng <span class="text-danger">*</span></label>
            <input type="number" id="quantity" name="quantity" class="form-control"
                   min="0" max="4294967295" step="1" value="<?= esc($val('quantity')) ?>" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Mô tả</label>
        <textarea id="description" name="description" rows="4" maxlength="10000" class="form-control"><?= esc($val('description')) ?></textarea>
    </div>

    <button type="submit" class="btn <?= esc($submitClass) ?>"><?= esc($submitLabel) ?></button>
    <a href="<?= site_url('books') ?>" class="btn btn-secondary">Quay lại</a>
</form>
