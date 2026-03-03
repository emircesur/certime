<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BadgeDirectory;
use App\Models\SkillTaxonomy;

/**
 * DirectoryController — Public-facing searchable badge directory
 */
class DirectoryController extends Controller
{
    /**
     * Browse badges
     */
    public function index()
    {
        $query = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;

        $model = new BadgeDirectory();
        $badges = $model->search($query, $category, $perPage, ($page - 1) * $perPage);
        $categories = $model->getCategories();
        $featured = empty($query) && empty($category) ? $model->getFeatured(6) : [];

        return $this->view('badge/directory', [
            'title' => 'Badge Directory',
            'badges' => $badges,
            'categories' => $categories,
            'featured' => $featured,
            'query' => $query,
            'category' => $category,
            'page' => $page,
        ]);
    }

    /**
     * Skill taxonomy browser
     */
    public function skills()
    {
        $currentCategory = trim($_GET['category'] ?? '');
        $query = trim($_GET['q'] ?? '');
        $framework = trim($_GET['framework'] ?? '');

        $model = new SkillTaxonomy();
        $skills = !empty($query) ? $model->search($query) : $model->getAll();

        // Filter by category if specified
        if (!empty($currentCategory)) {
            $skills = array_filter($skills, fn($s) => ($s['category'] ?? '') === $currentCategory);
            $skills = array_values($skills);
        }

        $categories = $model->getCategories();
        $frameworks = $model->getFrameworks();

        return $this->view('badge/skills', [
            'title' => 'Skill Taxonomy Browser',
            'skills' => $skills,
            'categories' => $categories,
            'frameworks' => $frameworks,
            'currentCategory' => $currentCategory,
            'query' => $query,
            'framework' => $framework,
        ]);
    }

    /**
     * Admin: Manage badge directory
     */
    public function manage()
    {
        $this->requireStaff();
        $model = new BadgeDirectory();
        $badges = $model->search('', '', 100, 0);

        return $this->view('admin/super/badge_directory', [
            'title' => 'Manage Badge Directory',
            'badges' => $badges,
        ]);
    }

    /**
     * Admin: Add badge to directory
     */
    public function addBadge()
    {
        $this->requireStaff();
        $this->requireCsrf();

        $model = new BadgeDirectory();
        $id = $model->create([
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'issuer_name' => trim($_POST['issuer_name'] ?? ''),
            'issuer_url' => trim($_POST['issuer_url'] ?? ''),
            'criteria_url' => trim($_POST['criteria_url'] ?? ''),
            'image_url' => trim($_POST['image_url'] ?? ''),
            'skill_codes' => trim($_POST['skill_codes'] ?? ''),
            'is_featured' => !empty($_POST['is_featured']),
        ]);

        if ($id) {
            flash('success', 'Badge added to directory.');
        } else {
            flash('error', 'Failed to add badge.');
        }
        $this->redirect('admin/directory');
    }

    /**
     * Admin: Remove badge from directory
     */
    public function removeBadge($id)
    {
        $this->requireStaff();
        $this->requireCsrf();
        
        $model = new BadgeDirectory();
        $model->delete((int)$id);
        flash('success', 'Badge removed from directory.');
        $this->redirect('admin/directory');
    }

    /**
     * Admin: Manage skill taxonomy
     */
    public function manageSkills()
    {
        $this->requireStaff();
        $model = new SkillTaxonomy();
        $skills = $model->getAll();

        return $this->view('admin/super/skill_taxonomy', [
            'title' => 'Manage Skill Taxonomy',
            'skills' => $skills,
        ]);
    }

    /**
     * Admin: Add skill
     */
    public function addSkill()
    {
        $this->requireStaff();
        $this->requireCsrf();

        $model = new SkillTaxonomy();
        $model->create(
            trim($_POST['code'] ?? ''),
            trim($_POST['name'] ?? ''),
            trim($_POST['category'] ?? ''),
            trim($_POST['framework'] ?? 'custom'),
            trim($_POST['description'] ?? '')
        );

        flash('success', 'Skill added.');
        $this->redirect('admin/skills');
    }

    /**
     * Admin: Link skill to credential
     */
    public function linkSkill()
    {
        $this->requireStaff();
        $this->requireCsrf();

        $credentialUid = trim($_POST['credential_uid'] ?? '');
        $skillCode = trim($_POST['skill_code'] ?? '');

        if (empty($credentialUid) || empty($skillCode)) {
            flash('error', 'Both credential UID and skill code are required.');
        } else {
            $model = new SkillTaxonomy();
            $model->linkToCredential($credentialUid, $skillCode);
            flash('success', 'Skill linked to credential.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            header('Location: ' . $referer);
            exit();
        }
        $this->redirect('admin/skills');
    }
}
