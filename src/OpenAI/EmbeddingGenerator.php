<?php
namespace AZ\AI\OpenAI;

use AZ\Project\Env;

class EmbeddingGenerator extends Model
{

    /**
     * Get default model.
     *
     * @return string
     */
    public function getModel(): string
    {
        $result = $this->model;

        if (!$result) {
            $result = (new Env())->get('OPENAI_EMBEDDING_MODEL', null);
        }

        if (!$result) {
            $result = 'text-embedding-3-small';
        }

        return $result;
    }


    /**
     * Function to get text embedding from OpenAI API
     *
     * @param string $text The text to embed.
     * @param string|null $model The model to use. Defaults to 'text-embedding-3-small'.
     * @return array|null The embedding vector or null on failure.
     * @throws Exception
     */
    public function generate(string $text, ?string $model = null): ?array
    {
        $url = "https://api.openai.com/v1/embeddings";

        if (!$model) {
            $model = $this->getModel();
        }

        $query = [
            'model' => $model,
            'input' => $text
        ];

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

        $start = microtime(true);

        // Execute request
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $this->delay = (microtime(true) - $start) * 1000;

        // Decode JSON response
        $result = json_decode($response, true);

        if (is_array($result)) {
            $this->onResponse($result, $query);
            $this->response = $result;
        }

        if (isset($result['error'])) {
            $message = $result['error']['message'] ?? 'Unknown remote error';
            throw new Exception($message, 1);
        }

        if (!isset($result['data'][0]['embedding'])) {
            throw new Exception('No embedding in API response.', 1);
        }

        return $result['data'][0]['embedding'];
    }    


}