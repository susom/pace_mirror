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

    public function createRequest($method, $url = '', array $options = [])
    {
        if(empty($options)) {
            $options = [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->getUsername() . ':' . $this->getPassword()),
                    'Accept' => 'application/json'
                ]
            ];
        }

        $response = parent::request($method, $url, $options);
        $code = $response->getStatusCode();

        if ($code == 200 || $code == 201 || $code == 202) {
            $content = $response->getBody()->getContents();
            if (is_array(json_decode($content, true))) {
                return json_decode($content, true);
            }

            return $content;
        } else {
            throw new \Exception("Request has failed: $response");
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

