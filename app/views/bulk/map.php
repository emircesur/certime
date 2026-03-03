<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <a href="<?= url('admin/bulk') ?>" class="text-decoration-none text-muted">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                Back to Bulk Issuance
            </a>
        </div>

        <h1 class="fw-bold mb-4">
            <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">map</span>
            Map CSV Columns
        </h1>

        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-body p-4">
                <p class="text-muted">
                    <strong><?= $totalRows ?></strong> rows found. Map your CSV columns to CertiMe fields below.
                </p>

                <form method="POST" action="<?= url('admin/bulk/process') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3 mb-4">
                        <?php
                        $fields = [
                            'email' => ['label' => 'Email *', 'required' => true],
                            'name' => ['label' => 'Full Name', 'required' => false],
                            'title' => ['label' => 'Credential Title *', 'required' => true],
                            'description' => ['label' => 'Description', 'required' => false],
                            'category' => ['label' => 'Category', 'required' => false],
                            'skills' => ['label' => 'Skills', 'required' => false],
                            'type' => ['label' => 'Credential Type', 'required' => false],
                            'credits' => ['label' => 'Credit Hours', 'required' => false],
                        ];
                        foreach ($fields as $key => $config):
                        ?>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><?= $config['label'] ?></label>
                            <select name="col_<?= $key ?>" class="form-select" <?= $config['required'] ? 'required' : '' ?>>
                                <option value="-1">— Skip —</option>
                                <?php foreach ($headers as $i => $h): ?>
                                <option value="<?= $i ?>" <?= strtolower(trim($h)) === $key ? 'selected' : '' ?>>
                                    <?= e($h) ?> (col <?= $i + 1 ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Preview -->
                    <h6 class="fw-semibold mb-2">Data Preview (first <?= count($preview) ?> rows)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered small">
                            <thead>
                                <tr>
                                    <?php foreach ($headers as $h): ?>
                                    <th class="bg-light"><?= e($h) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($preview as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                    <td><?= e($cell) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info d-flex align-items-center gap-2" style="border-radius:12px">
                        <span class="material-symbols-rounded">info</span>
                        <small>Users without accounts will be auto-registered. All credentials will be signed with Ed25519.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">play_arrow</span>
                            Issue <?= $totalRows ?> Credentials
                        </button>
                        <a href="<?= url('admin/bulk') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
