<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Dotenv\Dotenv;

class AIHelper
{
    private $client;
    private $apiKey;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();

        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        
        if (!$this->apiKey) {
            throw new \Exception("OpenAI API Key not found in environment variables.");
        }

        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout'  => 30.0,
        ]);
    }

    public function explainCode(string $code, string $language): array
    {
        $prompt = "Analyze the following {$language} code. Provide a plain English explanation (2-4 sentences) and a list of key parts (functions, classes, or important logic blocks). Return the result as a JSON object with keys 'explanation' (string) and 'key_parts' (array of strings).\n\nCode:\n```{$language}\n{$code}\n```";

        try {
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini', // Use a model version that definitely supports JSON mode
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful assistant that explains code snippets. You must output valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.5,
                    'max_tokens' => 300,
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['choices'][0]['message']['content'])) {
                $content = json_decode($body['choices'][0]['message']['content'], true);
                return [
                    'success' => true,
                    'explanation' => $content['explanation'] ?? 'No explanation provided.',
                    'key_parts' => $content['key_parts'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Invalid response structure from OpenAI API.'
                ];
            }

        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => 'API Request failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }
}
