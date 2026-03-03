<?php

namespace App\Lib\Tools;

use App\Models\Credential;

class GetUserCredentialsTool implements ToolInterface
{
    public function execute(...$args): string
    {
        $userId = $args[0];
        $credentialModel = new Credential();
        $credentials = $credentialModel->findByUser($userId);
        
        // Return as JSON to simulate a real tool call's output
        return json_encode($credentials);
    }

    public function getName(): string
    {
        return 'getUserCredentials';
    }

    public function getDescription(): string
    {
        return 'Gets the list of credentials for a given user ID.';
    }
}
