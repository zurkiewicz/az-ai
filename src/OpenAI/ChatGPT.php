<?php

namespace AZ\AI\OpenAI;

use AZ\Project\Env;

class ChatGPT
{

    /**
     * Model
     *
     * @var string|null
     */
    private $model;

    /**
     * Last API response.
     *
     * @var array|null
     */
    private $response;


    /**
     * Get las API response or null.
     *
     * @return array|null
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }



    /**
     * Get default model.
     *
     * @return string
     */
    public function getModel(): string
    {
        $result = $this->model;

        if (!$result) {
            $result = (new Env())->get('OPENAI_MODEL', null);
        }

        if (!$result) {
            $result = 'gpt-4.1-mini';
        }

        return $result;
    }



    /**
     * Set default model.
     *
     * @param string $model
     * @return void
     */
    public function setModel(string $model): void
    {

        $this->model = $model;
    }


    /**
     * Function to connect to OpenAI API
     *
     * @param string $prompt The text prompt to send
     * @param string $model  The model to use (left empty to default model use)
     * @return string Response text from OpenAI
     */
    function prompt(string $prompt, ?string $model = null): string
    {

        if (!$model) {

            $model = $this->getModel();
        }

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

            $data['model'] = $this->getModel();
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

        if (is_array($result)) {

            $this->response = $result;
            $this->onResponse($result);
        }

        if (isset($result['error'])) {

            $message = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown remote error';
            throw new Exception($message, 1);
        }


        if (!isset($result['choices'][0]['message']['content'])) {

            throw new Exception('No response from API.', 1);
        }

        return $result['choices'][0]['message']['content'];
    }

    /**
     * Called if the API responded.
     *
     * @param array $response
     * @return void
     */
    protected function onResponse(array $response): void
    {

    }
}
