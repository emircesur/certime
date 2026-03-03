<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TeamMember;
use App\Models\User;

class PlanController extends Controller
{
    /**
     * Public pricing page
     */
    public function pricing()
    {
        $planModel = new Plan();
        $plans = $planModel->getAll();

        $currentSub = null;
        if (isLoggedIn()) {
            $subModel = new Subscription();
            $currentSub = $subModel->findByUser(currentUserId());
        }

        return $this->view('plans/pricing', [
            'title' => 'Pricing Plans',
            'plans' => $plans,
            'currentSub' => $currentSub,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $planSlug = trim($_POST['plan'] ?? '');
        $billingCycle = trim($_POST['billing_cycle'] ?? 'monthly');

        $planModel = new Plan();
        $plan = $planModel->findBySlug($planSlug);

        if (!$plan) {
            flash('error', 'Invalid plan selected.');
            $this->redirect('pricing');
            return;
        }

        if ((float)$plan['price_monthly'] > 0) {
            // Paid plan - for now, just activate (Stripe integration later)
            flash('info', 'Payment integration coming soon. Plan activated as trial.');
        }

        $subModel = new Subscription();
        $result = $subModel->create(currentUserId(), (int)$plan['id'], $billingCycle);

        if ($result) {
            flash('success', 'Subscribed to ' . $plan['name'] . ' plan successfully!');
        } else {
            flash('error', 'Failed to subscribe. Please try again.');
        }
        $this->redirect('pricing');
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $subModel = new Subscription();
        if ($subModel->cancel(currentUserId())) {
            flash('success', 'Subscription cancelled. You are now on the Free plan.');
        } else {
            flash('error', 'Failed to cancel subscription.');
        }
        $this->redirect('pricing');
    }

    /**
     * Team management page
     */
    public function team()
    {
        $this->requireAuth();

        $subModel = new Subscription();
        $currentSub = $subModel->findByUser(currentUserId());

        $teamModel = new TeamMember();
        $members = $teamModel->getTeamByOwner(currentUserId());
        $myTeams = $teamModel->getTeamsForUser(currentUserId());

        return $this->view('plans/team', [
            'title' => 'Team Management',
            'subscription' => $currentSub,
            'members' => $members,
            'myTeams' => $myTeams,
        ]);
    }

    /**
     * Add team member
     */
    public function addMember()
    {
        $this->requireAuth();
        $this->requireCsrf();

        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'member');

        if (empty($email)) {
            flash('error', 'Please provide an email address.');
            $this->redirect('team');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            flash('error', 'No user found with that email. They must register first.');
            $this->redirect('team');
            return;
        }

        // Check subscription allows team members
        $subModel = new Subscription();
        $currentSub = $subModel->findByUser(currentUserId());
        $teamModel = new TeamMember();
        $memberCount = $teamModel->countMembers(currentUserId());

        $maxUsers = $currentSub ? (int)$currentSub['max_users'] : 1;
        if ($memberCount >= $maxUsers) {
            flash('error', 'Team member limit reached. Upgrade your plan for more members.');
            $this->redirect('team');
            return;
        }

        $result = $teamModel->addMember(currentUserId(), (int)$user['id'], $role);
        if ($result) {
            flash('success', $user['username'] . ' added to your team!');
        } else {
            flash('error', 'User is already a team member.');
        }
        $this->redirect('team');
    }

    /**
     * Remove team member
     */
    public function removeMember($id)
    {
        $this->requireAuth();
        $this->requireCsrf();

        $teamModel = new TeamMember();
        if ($teamModel->removeMember(currentUserId(), (int)$id)) {
            flash('success', 'Team member removed.');
        } else {
            flash('error', 'Failed to remove team member.');
        }
        $this->redirect('team');
    }
}
