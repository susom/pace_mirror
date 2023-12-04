<?php

namespace Stanford\PACE;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client extends GuzzleHttpClient
{
    private ?string $username;
    private ?string $password;

    public function __construct(?string $username, ?string $password)
    {
        parent::__construct();
        if($username && $password)
            $this->setCredentials($username, $password);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException|\Exception
     */
    public function createRequest(string $method, string $url = '', array $options = []): string
    {
        $options = $this->setDefaultOptions($options);

        try {
            $response = parent::request($method, $url, $options);
            $code = $response->getStatusCode();

            if (in_array($code, [200, 201, 202])) {
                return $response->getBody()->getContents();
            } else {
                throw new \Exception("Request has failed unexpectedly: $response");
            }
        } catch (GuzzleException $e) {
            throw new \Exception("Guzzle request failed: " . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set default options if none provided
     *
     * @param array $options
     * @return array
     */
    private function setDefaultOptions(array $options): array
    {
        if (empty($options)) {
            $options = [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->getUsername() . ':' . $this->getPassword()),
                    'Accept' => 'application/json',
                ],
            ];
        }
        return $options;
    }

    /**
     * Set the username and password
     *
     * @param string $username
     * @param string $password
     */
    private function setCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }
}
