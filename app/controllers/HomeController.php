<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [];
        if (isLoggedIn()) {
            $credModel = new \App\Models\Credential();
            $stats['my_credentials'] = $credModel->countByUser(currentUserId());
        }
        
        return $this->view('home/index', [
            'title' => 'Welcome',
            'stats' => $stats
        ]);
    }
}
