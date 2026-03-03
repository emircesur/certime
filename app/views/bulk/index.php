<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">upload_file</span>
                    Bulk Issuance Engine
                </h1>
                <p class="text-muted">Issue credentials to many recipients at once via CSV upload</p>
            </div>
            <a href="<?= url('admin') ?>" class="btn btn-outline-secondary rounded-pill">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                Back to Admin
            </a>
        </div>

        <!-- Upload CSV -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">table_view</span>
                    Upload CSV File
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= url('admin/bulk/upload') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row align-items-end g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <small class="text-muted">CSV must have a header row. Required columns: email, title.</small>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary rounded-pill w-100">
                                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">upload</span>
                                Upload & Map Columns
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-4">
                    <h6 class="fw-semibold">CSV Template</h6>
                    <div class="bg-dark text-light p-3 rounded-3 small font-monospace">
                        email,name,title,description,category,skills,type,credits<br>
                        john@example.com,John Doe,Web Development,Completed web dev course,technology,"HTML,CSS,JS",certificate,3<br>
                        jane@example.com,Jane Smith,Data Science,Data science fundamentals,data,"Python,ML",certificate,4
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Jobs -->
        <?php if (!empty($jobs)): ?>
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-semibold mb-0">
                    <span class="material-symbols-rounded" style="font-size:20px;vertical-align:-4px">history</span>
                    Recent Bulk Jobs
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>File</th>
                                <th>Created By</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Success</th>
                                <th class="text-center">Errors</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td class="text-muted">#<?= $j['id'] ?></td>
                                <td><code><?= e($j['filename']) ?></code></td>
                                <td class="text-muted"><?= e($j['creator_name'] ?? 'Unknown') ?></td>
                                <td class="text-center"><?= $j['total_rows'] ?></td>
                                <td class="text-center text-success fw-semibold"><?= $j['success_count'] ?></td>
                                <td class="text-center text-danger fw-semibold"><?= $j['error_count'] ?></td>
                                <td class="text-center">
                                    <?php
                                    $statusColors = ['pending' => 'secondary', 'processing' => 'info', 'completed' => 'success', 'failed' => 'danger'];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$j['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$j['status']] ?? 'secondary' ?>">
                                        <?= ucfirst($j['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('M j, Y H:i', strtotime($j['created_at'])) ?></td>
                                <td>
                                    <a href="<?= url('admin/bulk/job/' . $j['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <span class="material-symbols-rounded" style="font-size:16px">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
