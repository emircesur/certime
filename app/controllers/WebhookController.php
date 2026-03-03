<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Webhook;

/**
 * WebhookController — CRUD & management UI for webhook endpoints
 */
class WebhookController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
    }

    public function index()
    {
        $model = new Webhook();
        $webhooks = $model->findByUser(currentUserId());

        return $this->view('admin/webhooks/index', [
            'title' => 'Webhooks',
            'webhooks' => $webhooks,
        ]);
    }

    public function create()
    {
        return $this->view('admin/webhooks/create', [
            'title' => 'Create Webhook',
            'events' => [
                'credential.issued',
                'credential.revoked',
                'credential.expired',
                'credential.renewed',
                'endorsement.created',
                'endorsement.approved',
                'badge.claimed',
                'user.registered',
            ],
        ]);
    }

    public function store()
    {
        $this->requireCsrf();

        $url = trim($_POST['url'] ?? '');
        $events = $_POST['events'] ?? [];
        if (is_string($events)) $events = explode(',', $events);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            flash('error', 'A valid URL is required.');
            $this->redirect('webhooks/create');
            return;
        }

        $model = new Webhook();
        $id = $model->create(currentUserId(), $url, $events);

        if ($id) {
            flash('success', 'Webhook created. Secret: ' . $model->findById($id)['secret']);
        } else {
            flash('error', 'Failed to create webhook.');
        }
        $this->redirect('webhooks');
    }

    public function toggle($id)
    {
        $this->requireCsrf();
        $model = new Webhook();
        $model->toggle((int)$id);
        flash('success', 'Webhook toggled.');
        $this->redirect('webhooks');
    }

    public function delete($id)
    {
        $this->requireCsrf();
        $model = new Webhook();
        $model->delete((int)$id);
        flash('success', 'Webhook deleted.');
        $this->redirect('webhooks');
    }

    public function events($id)
    {
        $model = new Webhook();
        $events = $model->getEvents((int)$id);

        return $this->view('admin/webhooks/events', [
            'title' => 'Webhook Events',
            'webhookId' => $id,
            'events' => $events,
        ]);
    }

    public function test($id)
    {
        $this->requireCsrf();
        $model = new Webhook();
        $webhook = $model->findById((int)$id);

        if (!$webhook) {
            flash('error', 'Webhook not found.');
            $this->redirect('webhooks');
            return;
        }

        // Send a test ping
        $payload = json_encode([
            'event' => 'webhook.test',
            'timestamp' => date('c'),
            'data' => ['message' => 'This is a test webhook from CertiMe.'],
        ]);

        $signature = hash_hmac('sha256', $payload, $webhook['secret']);

        $ch = curl_init($webhook['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-CertiMe-Signature: sha256=' . $signature,
                'X-CertiMe-Event: webhook.test',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            flash('error', "Test failed: {$error}");
        } else {
            flash('success', "Test sent. Response: HTTP {$httpCode}");
        }
        $this->redirect('webhooks');
    }
}
