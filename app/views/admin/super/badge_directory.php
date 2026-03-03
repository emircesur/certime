<?php require APP_PATH . '/views/partials/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2"><?php require APP_PATH . '/views/admin/partials/nav.php'; ?></div>
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4">Manage Badge Directory</h4>

            <!-- Add Badge Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Add Badge to Public Directory</h6>
                    <form method="POST" action="<?= url('admin/directory/add') ?>">
                        <?= csrfField() ?>
                        <div class="row g-2">
                            <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Badge name" required></div>
                            <div class="col-md-2"><input type="text" name="category" class="form-control" placeholder="Category"></div>
                            <div class="col-md-2"><input type="text" name="issuer_name" class="form-control" placeholder="Issuer name"></div>
                            <div class="col-md-3"><input type="text" name="description" class="form-control" placeholder="Description"></div>
                            <div class="col-md-1"><div class="form-check mt-2"><input type="checkbox" name="is_featured" class="form-check-input" value="1"><label class="form-check-label small">Featured</label></div></div>
                            <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>Category</th><th>Issuer</th><th>Featured</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($badges as $b): ?>
                        <tr>
                            <td><strong><?= e($b['name']) ?></strong></td>
                            <td><?= e($b['category'] ?? '') ?></td>
                            <td><?= e($b['issuer_name'] ?? '') ?></td>
                            <td><?= $b['is_featured'] ? '⭐' : '' ?></td>
                            <td>
                                <form method="POST" action="<?= url('admin/directory/' . $b['id'] . '/remove') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
