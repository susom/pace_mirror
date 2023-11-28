<?php

namespace Stanford\PACE;

use function PHPUnit\Framework\isEmpty;

class Client extends \GuzzleHttp\Client
{
    private string $url;
    private string $username;
    private string $password;

    public function __construct(string $user, string $pass)
    {
        parent::__construct();
        $this->setUsername($user);
        $this->setPassword($pass);
    }

    /**
     * @param $method
     * @param string $url
     * @param array $options
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createRequest($method, string $url = '', array $options = []): string
    {
        //Set default options if none provided
        if(empty($options)) {
            $options = [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->getUsername() . ':' . $this->getPassword()),
                    'Accept' => 'application/json'
                ]
            ];
        }

        // Make HTTP request
        $response = parent::request($method, $url, $options);
        $code = $response->getStatusCode();

        // Check if the response status code is in the success range
        if (in_array($code, [200, 201, 202])) {
            // Retrieve and return the response content
            return $response->getBody()->getContents();
        } else {
            throw new \Exception("Request has failed unexpectedly: $response");
        }
    }


    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

}

