<?php

namespace AZ\AI\OpenAI;

use AZ\Project\Env;

abstract class Model
{

    /**
     * Model
     *
     * @var string|null
     */
    protected $model;
    
    /**
     * Provider delay in milliseconds
     *
     * @var int|null
     */
    protected int $delay;

    /**
     * Last API response.
     *
     * @var array|null
     */
    protected $response;


    /**
     * Get last API response or null.
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
    abstract public function getModel(): string;


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
     * Get provider delay in milliseconds fot last request.
     *
     * @return int|null
     */
    public function getDelay(): ?int
    {
        return $this->delay;
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
