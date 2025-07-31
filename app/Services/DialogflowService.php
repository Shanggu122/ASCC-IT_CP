<?php
namespace App\Services;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;

class DialogflowService
{
    private SessionsClient $client;
    private string $projectId;
    private string $languageCode = 'en-US';

    public function __construct()
    {
        // 1) Read your .env project ID
        $this->projectId = env('DIALOGFLOW_PROJECT_ID', 'ascc-itbot-dpkw');

        // 2) Path to your JSON key (ensure this filename is correct)
        $creds = storage_path('app/ascc-itbot-dpkw-c4c081008227.json');

        // 3) Instantiate the Dialogflow client once
        $this->client = new SessionsClient([
            'credentials' => $creds,
        ]);
    }

    public function detectIntent(string $text, string $sessionId): string
    {
        // 4) Build session path
        $session = $this->client->sessionName($this->projectId, $sessionId);

        // 5) Prepare the text input
        $queryInput = (new QueryInput())
            ->setText(
                (new TextInput())
                    ->setText($text)
                    ->setLanguageCode($this->languageCode)
            );

        // 6) Call Dialogflow
        $response = $this->client->detectIntent($session, $queryInput);

        // 7) Return only the fulfillment text
        return $response->getQueryResult()->getFulfillmentText();
    }
}
