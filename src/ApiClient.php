<?php

declare(strict_types=1);

namespace Elective\ApiClients;

use Elective\ApiClients\Result;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

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

    public const ACTION_VIEW        = 'view';
    public const ACTION_CREATE      = 'create';
    public const ACTION_EDIT        = 'edit';
    public const ACTION_DELETE      = 'delete';
    public const ACTION_UNDELETE    = 'undelete';
    public const ACTION_OPERATOR    = 'operator';
    public const ACTION_MASTER      = 'master';
    public const ACTION_OWNER       = 'owner';

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

        // Set message
        if (!$result->isSuccessful()) {
            $result->setErrorMessage($this->messageFromStatusCode($result->getCode()));

            if (isset($result->getData()->message)) {
                $result->setErrorMessage($result->getErrorMessage() . '. '. $result->getData()->message);
            }
        }

        return $result;
    }

    public function messageFromStatusCode($statusCode)
    {
        switch ($statusCode) {
            case Response::HTTP_OK:
                return 'OK';
                break;

            case Response::HTTP_BAD_REQUEST:
                return 'Bad request';
                break;

            case Response::HTTP_UNAUTHORIZED:
                return 'Unauthorised operation';
                break;

            case Response::HTTP_FORBIDDEN:
                return 'Action was Forbidden';
                break;

            case Response::HTTP_NOT_FOUND:
                return 'Resource was Not found';
                break;

            case Response::HTTP_INTERNAL_SERVER_ERROR:
                return 'Problem with ACL Api';
                break;

            default:
                return 'Unspecified';
                break;
        }
    }
}
