<?php

declare(strict_types=1);

namespace Elective\ApiClients;

use Elective\ApiClients\Result;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Elective\ApiClients\ApiClient
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class ApiClient
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Token
     */
    private $token;

    public function __construct(HttpClientInterface $client, bool $isEnabled = true)
    {
        $this->client = $client;
        $this->isEnabled = $isEnabled;
    }

    public function getClient(): ?HttpClientInterface
    {
        return $this->client;
    }

    public function setClient(?HttpClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(?bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function handleRequest($method, $url, $options): Result
    {
        $result = new Result();

        if (!$this->getIsEnabled()) {
            // Fake response
            $result->setCode(200);

            return $result;
        }

        $response = $this->getClient()->request($method, $url, $options);

        // Set Code
        $result->setCode($response->getStatusCode());

        // Set Data
        // Try auto parsing JSON
        if($response->getInfo('content_type') == 'application/json') {
            $result->setData(json_decode($response->getContent(false)));
        } else {
            $result->setData($response->getContent(false));
        }

        return $result;
    }
}
