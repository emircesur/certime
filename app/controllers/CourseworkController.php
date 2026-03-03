<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Coursework;

class CourseworkController extends Controller
{
    protected Coursework $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new Coursework();
    }

    /**
     * My coursework list
     */
    public function index()
    {
        $courses = $this->model->findByUser(currentUserId());
        $gpa = $this->model->getGPA(currentUserId());
        $totalCredits = $this->model->getTotalCredits(currentUserId());
        $terms = $this->model->getTerms(currentUserId());

        return $this->view('coursework/index', [
            'title' => 'My Coursework',
            'courses' => $courses,
            'gpa' => $gpa,
            'totalCredits' => $totalCredits,
            'terms' => $terms,
        ]);
    }

    /**
     * Add coursework form
     */
    public function create()
    {
        return $this->view('coursework/create', [
            'title' => 'Add Coursework',
        ]);
    }

    /**
     * Store new coursework
     */
    public function store()
    {
        $this->requireCsrf();

        $courseCode = trim($_POST['course_code'] ?? '');
        $courseName = trim($_POST['course_name'] ?? '');
        $credits = (float)($_POST['credits'] ?? 0);
        $grade = trim($_POST['grade'] ?? '');
        $term = trim($_POST['term'] ?? '');
        $institution = trim($_POST['institution'] ?? '');

        if (empty($courseCode) || empty($courseName) || $credits <= 0) {
            flash('error', 'Course code, name, and credits are required.');
            $this->redirect('coursework/create');
            return;
        }

        $id = $this->model->create(
            currentUserId(), $courseCode, $courseName, $credits,
            $grade, $term, $institution
        );

        if ($id) {
            flash('success', 'Coursework added successfully!');
            $this->redirect('coursework');
        } else {
            flash('error', 'Failed to add coursework.');
            $this->redirect('coursework/create');
        }
    }

    /**
     * Edit coursework
     */
    public function edit($id)
    {
        $course = $this->model->findById((int)$id);
        if (!$course || (int)$course['user_id'] !== currentUserId()) {
            return $this->view('errors/404', ['title' => 'Not Found']);
        }

        return $this->view('coursework/edit', [
            'title' => 'Edit Coursework',
            'course' => $course,
        ]);
    }

    /**
     * Update coursework
     */
    public function update($id)
    {
        $this->requireCsrf();

        $course = $this->model->findById((int)$id);
        if (!$course || (int)$course['user_id'] !== currentUserId()) {
            flash('error', 'Not found.');
            $this->redirect('coursework');
            return;
        }

        $data = [
            'course_code' => trim($_POST['course_code'] ?? $course['course_code']),
            'course_name' => trim($_POST['course_name'] ?? $course['course_name']),
            'credits' => (float)($_POST['credits'] ?? $course['credits']),
            'grade' => trim($_POST['grade'] ?? ''),
            'term' => trim($_POST['term'] ?? ''),
            'institution' => trim($_POST['institution'] ?? ''),
            'status' => trim($_POST['status'] ?? 'in_progress'),
        ];

        if ($this->model->update((int)$id, $data)) {
            flash('success', 'Coursework updated!');
        } else {
            flash('error', 'Failed to update.');
        }
        $this->redirect('coursework');
    }

    /**
     * Delete coursework
     */
    public function delete($id)
    {
        $this->requireCsrf();

        $course = $this->model->findById((int)$id);
        if (!$course || (int)$course['user_id'] !== currentUserId()) {
            flash('error', 'Not found.');
            $this->redirect('coursework');
            return;
        }

        $this->model->delete((int)$id);
        flash('success', 'Coursework removed.');
        $this->redirect('coursework');
    }
}
