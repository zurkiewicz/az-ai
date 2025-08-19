<?php

namespace Az\AI\OpenAI;

use Exception;
use Az\Project\Env;


class ChatGPT
{

    /**
     * 
     */
    const DEFAULT_MODEL = 'gpt-4o-mini';


    /**
     * Function to connect to OpenAI API
     *
     * @param string $prompt The text prompt to send
     * @param string $model  The model to use (default: gpt-4o-mini)
     * @return string Response text from OpenAI
     */
    function prompt(string $prompt, string $model = self::DEFAULT_MODEL): string
    {

        $currentDateTime = date("Y-m-d H:i:s");

        $data = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant. Current date and time: {$currentDateTime}"],
                ["role" => "user", "content" => $prompt]
            ]
        ];    
        
        return $this->completions($data, $model);
        
    }


    /**
     * Function to connect to OpenAI API
     *
     * @param array $data The data to send
     * @return string Response text from OpenAI
     * @throws Exception
     */
    function completions(array $data): string
    {
        $url = "https://api.openai.com/v1/chat/completions";

        if (!isset($data['model'])) {

            $data['model'] = self::DEFAULT_MODEL;
        }

        $token = (new Env())->get('OPENAI_TOKEN');

        if (!$token) {

            throw new Exception("No OPENAI_TOKEN found in .env", 1); 
        }
        
        // Initialize curl
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token,
        ]);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute request
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Decode JSON response
        $result = json_decode($response, true);

        return $result['choices'][0]['message']['content'] ?? throw new Exception('No response from API.', 1);
        
    }
}