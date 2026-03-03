<?php require APP_PATH . '/views/partials/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold">
                    <span class="material-symbols-rounded" style="font-size:36px;vertical-align:-6px;color:var(--md-primary)">school</span>
                    My Coursework
                </h1>
                <p class="text-muted">Track your academic courses, grades, and progress</p>
            </div>
            <a href="<?= url('coursework/create') ?>" class="btn btn-primary rounded-pill">
                <span class="material-symbols-rounded" style="font-size:18px;vertical-align:-3px">add</span>
                Add Course
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4 text-center">
                        <span class="material-symbols-rounded text-primary mb-2" style="font-size:36px">grade</span>
                        <div class="display-6 fw-bold"><?= number_format($gpa, 2) ?></div>
                        <div class="text-muted small">Cumulative GPA</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4 text-center">
                        <span class="material-symbols-rounded text-success mb-2" style="font-size:36px">token</span>
                        <div class="display-6 fw-bold"><?= number_format($totalCredits, 1) ?></div>
                        <div class="text-muted small">Total Credits Earned</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="border-radius:16px">
                    <div class="card-body p-4 text-center">
                        <span class="material-symbols-rounded text-info mb-2" style="font-size:36px">menu_book</span>
                        <div class="display-6 fw-bold"><?= count($courses) ?></div>
                        <div class="text-muted small">Total Courses</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($courses)): ?>
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="card-body text-center py-5">
                <span class="material-symbols-rounded text-muted mb-3" style="font-size:64px">school</span>
                <h4>No coursework added yet</h4>
                <p class="text-muted">Start tracking your academic courses and build your transcript.</p>
                <a href="<?= url('coursework/create') ?>" class="btn btn-primary rounded-pill">Add First Course</a>
            </div>
        </div>
        <?php else: ?>

        <!-- Filter by Term -->
        <?php if (!empty($terms)): ?>
        <div class="mb-3">
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 cursor-pointer term-filter active" data-term="all">All Terms</span>
                <?php foreach ($terms as $t): ?>
                <span class="badge bg-light text-dark px-3 py-2 cursor-pointer term-filter" data-term="<?= e($t) ?>"><?= e($t) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Course Table -->
        <div class="card shadow-sm border-0" style="border-radius:16px">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Course Name</th>
                            <th>Institution</th>
                            <th>Term</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr class="course-row" data-term="<?= e($c['term'] ?? '') ?>">
                            <td class="ps-4"><code class="fw-semibold"><?= e($c['course_code']) ?></code></td>
                            <td><strong><?= e($c['course_name']) ?></strong></td>
                            <td class="text-muted small"><?= e($c['institution'] ?: '—') ?></td>
                            <td class="text-muted small"><?= e($c['term'] ?: '—') ?></td>
                            <td class="text-center"><?= number_format((float)$c['credits'], 1) ?></td>
                            <td class="text-center">
                                <?php if ($c['grade']): ?>
                                <span class="badge bg-<?= (float)($gradeGPA = ['A+'=>4,'A'=>4,'A-'=>3.7,'B+'=>3.3,'B'=>3,'B-'=>2.7,'C+'=>2.3,'C'=>2,'C-'=>1.7,'D+'=>1.3,'D'=>1,'D-'=>0.7,'F'=>0][$c['grade']] ?? 0) >= 3 ? 'success' : ((float)($gradeGPA ?? 0) >= 2 ? 'warning' : 'danger') ?>-subtle text-<?= (float)($gradeGPA ?? 0) >= 3 ? 'success' : ((float)($gradeGPA ?? 0) >= 2 ? 'warning' : 'danger') ?>">
                                    <?= e($c['grade']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                $statusColors = ['completed' => 'success', 'in_progress' => 'info', 'withdrawn' => 'danger', 'planned' => 'secondary'];
                                $statusLabels = ['completed' => 'Completed', 'in_progress' => 'In Progress', 'withdrawn' => 'Withdrawn', 'planned' => 'Planned'];
                                $st = $c['status'] ?? 'in_progress';
                                ?>
                                <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>-subtle text-<?= $statusColors[$st] ?? 'secondary' ?>"><?= $statusLabels[$st] ?? ucfirst($st) ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('coursework/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <span class="material-symbols-rounded" style="font-size:16px">edit</span>
                                    </a>
                                    <form method="POST" action="<?= url('coursework/' . $c['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this course?')">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                            <span class="material-symbols-rounded" style="font-size:16px">close</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('.term-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.term-filter').forEach(b => { b.classList.remove('active', 'bg-primary-subtle', 'text-primary'); b.classList.add('bg-light', 'text-dark'); });
        this.classList.add('active', 'bg-primary-subtle', 'text-primary');
        this.classList.remove('bg-light', 'text-dark');
        const term = this.dataset.term;
        document.querySelectorAll('.course-row').forEach(row => {
            row.style.display = (term === 'all' || row.dataset.term === term) ? '' : 'none';
        });
    });
});
</script>

<?php require APP_PATH . '/views/partials/footer.php'; ?>
