<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-4">
                    <a href="<?= url('coursework') ?>" class="text-decoration-none text-muted">
                        <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">arrow_back</span>
                        Back to Coursework
                    </a>
                </div>

                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h2 class="fw-bold mb-1">
                            <span class="material-symbols-rounded" style="font-size:28px;vertical-align:-5px;color:var(--md-primary)">edit</span>
                            Edit Coursework
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?= url('coursework/' . $course['id'] . '/update') ?>">
                            <?= csrfField() ?>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Course Code *</label>
                                    <input type="text" name="course_code" class="form-control" value="<?= e($course['course_code']) ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Course Name *</label>
                                    <input type="text" name="course_name" class="form-control" value="<?= e($course['course_name']) ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Credits *</label>
                                    <input type="number" name="credits" class="form-control" step="0.5" min="0.5" max="20" value="<?= e($course['credits']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Grade</label>
                                    <select name="grade" class="form-select">
                                        <option value="">Not Yet Graded</option>
                                        <?php foreach (['A+','A','A-','B+','B','B-','C+','C','C-','D+','D','D-','F','P','W'] as $g): ?>
                                        <option value="<?= $g ?>" <?= ($course['grade'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Term</label>
                                    <input type="text" name="term" class="form-control" value="<?= e($course['term'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach (['in_progress' => 'In Progress', 'completed' => 'Completed', 'planned' => 'Planned', 'withdrawn' => 'Withdrawn'] as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($course['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Institution</label>
                                <input type="text" name="institution" class="form-control" value="<?= e($course['institution'] ?? '') ?>">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">save</span>
                                    Update Course
                                </button>
                                <a href="<?= url('coursework') ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
