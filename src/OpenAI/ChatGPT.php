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
     * @return string|null Response text from OpenAI or null if canceled.
     */
    function prompt(string $prompt, ?string $model = null): ?string
    {

        if (!$model) {

            $model = $this->getModel();
        }

        $currentDateTime = date("Y-m-d H:i:s");

        $query = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant. Current date and time: {$currentDateTime}"],
                ["role" => "user", "content" => $prompt]
            ]
        ];

        return $this->completions($query, $model);
    }


    /**
     *
     * @return string
     * @throws Exception
     */
    protected function getToken(): string {

        $result = (new Env())->get('OPENAI_TOKEN');

        if (!$result) {

            throw new Exception("No OPENAI_TOKEN found in .env", 1);
        }        

        return $result;
    }



    /**
     * Function to connect to OpenAI API
     *
     * @param array $query The data to send.
     * @return string|null Response text from OpenAI or null if canceled.
     * @throws Exception
     */    
    function completions(array $query): ?string
    {
        $url = "https://api.openai.com/v1/chat/completions";

        if (!isset($query['model'])) {

            $query['model'] = $this->getModel();
        }

        if (!$this->onQuery($query)) {

            return null;
        }

        $token = $this->getToken();

        // Initialize curl
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $token,
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));

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
            $this->onResponse($result, $query);
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
     * Called before API query.
     *
     * @param array $query
     * @return bool Put false to cancel the query.
     */
    protected function onQuery(array & $query): bool
    {

        return true;
    }    

    /**
     * Called if the API responded.
     *
     * @param array $response
     * @param array $query
     * @return void
     */
    protected function onResponse(array & $response, array & $query): void
    {

    }
}
