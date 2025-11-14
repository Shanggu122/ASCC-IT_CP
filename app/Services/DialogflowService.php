<?php

namespace App\Services;

use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Illuminate\Support\Facades\Log;

class DialogflowService
{
    private ?string $projectId;
    private string $languageCode;
    private ?string $credentialsPath;

    public function __construct()
    {
        $config = config("services.dialogflow");
        $this->projectId = $config["project_id"] ?? null;
        $this->languageCode = $config["language"] ?? "en-US";
        $this->credentialsPath = $this->resolveCredentialsPath($config["credentials_path"] ?? null);
    }

    public function detectIntent(string $text, string $sessionId): ?string
    {
        if (empty($this->projectId)) {
            Log::warning("Dialogflow: missing project id");
            return null;
        }

        if (empty($this->credentialsPath) || !file_exists($this->credentialsPath)) {
            Log::warning("Dialogflow: credentials file unavailable", [
                "path" => $this->credentialsPath,
            ]);
            return null;
        }

        try {
            $client = new SessionsClient($this->clientOptions());
            $session = $client->sessionName($this->projectId, $sessionId);

            $textInput = new TextInput()->setText($text)->setLanguageCode($this->languageCode);

            $queryInput = new QueryInput()->setText($textInput);

            $response = $client->detectIntent($session, $queryInput);
            $client->close();

            return $response->getQueryResult()->getFulfillmentText();
        } catch (\Throwable $e) {
            Log::error("Dialogflow detectIntent failed", [
                "error" => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function clientOptions(): array
    {
        $options = [
            "credentials" => $this->credentialsPath,
        ];

        if (config("services.dialogflow.transport", "rest") === "rest") {
            $options["transport"] = "rest";
        }

        return $options;
    }

    private function resolveCredentialsPath(?string $configuredPath): ?string
    {
        if ($configuredPath && file_exists($configuredPath)) {
            return $configuredPath;
        }

        $encoded = env("DIALOGFLOW_KEY_B64");
        if ($encoded) {
            $decoded = base64_decode($encoded, true);
            if ($decoded === false) {
                Log::error("Dialogflow: failed to decode base64 key");
                return null;
            }

            $target = storage_path("app/dialogflow-runtime-key.json");
            if (file_put_contents($target, $decoded) === false) {
                Log::error("Dialogflow: unable to write decoded key file");
                return null;
            }

            return $target;
        }

        $defaultPath = storage_path("app/ascc-itbot-dpkw-c4c081008227.json");
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        return null;
    }
}
