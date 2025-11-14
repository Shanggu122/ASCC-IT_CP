<?php
namespace App\Services;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use RuntimeException;

class DialogflowService
{
    private SessionsClient $client;
    private string $projectId;
    private string $languageCode = "en-US";

    public function __construct()
    {
        $this->projectId = env("DIALOGFLOW_PROJECT_ID", "ascc-itbot-dpkw");

        $credentialPath = null;
        $encoded = env("DIALOGFLOW_KEY_B64");
        if ($encoded) {
            $decoded = base64_decode($encoded, true);
            if ($decoded === false) {
                throw new RuntimeException("Invalid Dialogflow key payload.");
            }
            $target = storage_path("app/dialogflow-runtime-key.json");
            if (file_put_contents($target, $decoded) === false) {
                throw new RuntimeException("Unable to persist Dialogflow key to storage.");
            }
            $credentialPath = $target;
        }

        if (!$credentialPath) {
            $configuredPath = env("DIALOGFLOW_KEY_PATH");
            if ($configuredPath && file_exists($configuredPath)) {
                $credentialPath = $configuredPath;
            }
        }

        if (!$credentialPath) {
            $defaultPath = storage_path("app/ascc-itbot-dpkw-c4c081008227.json");
            if (file_exists($defaultPath)) {
                $credentialPath = $defaultPath;
            }
        }

        if (!$credentialPath || !file_exists($credentialPath)) {
            throw new RuntimeException(
                "Dialogflow credentials missing. Provide key file or set DIALOGFLOW_KEY_B64 environment variable.",
            );
        }

        $this->client = new SessionsClient([
            "credentials" => $credentialPath,
        ]);
    }

    public function detectIntent(string $text, string $sessionId): string
    {
        // 4) Build session path
        $session = $this->client->sessionName($this->projectId, $sessionId);

        // 5) Prepare the text input
        $queryInput = new QueryInput()->setText(
            new TextInput()->setText($text)->setLanguageCode($this->languageCode),
        );

        // 6) Call Dialogflow
        $response = $this->client->detectIntent($session, $queryInput);

        // 7) Return only the fulfillment text
        return $response->getQueryResult()->getFulfillmentText();
    }
}
