<?php

declare(strict_types=1);

namespace Elective\ApiClients;

use Elective\ApiClients\Result;
use Elective\SecurityBundle\Token\TokenDecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

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

    public function __construct(
        HttpClientInterface $client,
        bool $isEnabled = true,
        TokenDecoderInterface $tokenDecoder
    ) {
        $this->client       = $client;
        $this->isEnabled    = $isEnabled;
        $this->tokenDecoder = $tokenDecoder;
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

    public function getTokenDecoder(): TokenDecoderInterface
    {
        return $this->tokenDecoder;
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
        if(str_contains($response->getInfo('content_type'), 'application/json')) {
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

        // Set  total results of data
        try {
            $headers = $response->getHeaders();
            if (isset($headers['x-results-total'])) {
                $result->setHeader('X-Results-Total', $headers['x-results-total'][0]);
            }
        } catch (\Throwable $th) {
            // We got an error here, let the client deal with it
        }

        // Set info
        if (isset($result->getData()->message)) {
            $result->setInfo($result->getData()->message);
        }

        // Set message type from status code
        $result->setMessage($this->messageFromStatusCode($result->getCode()));
    
        return $result;
    }

    public function getOrganisationFromToken(string $token): string {
        try {
            $attributes     = $this->getTokenDecoder()->decodeJWTUserToken($token);
            $organisationId = isset($attributes['organisation']) ? $attributes['organisation'] : '';
        } catch (TokenDecoderException $e) {
            return '';
        }

        return $organisationId;
    }

    public function getAuthorisationHeader(RequestStack $request) {
        $token = $request->getCurrentRequest() ? $request->getCurrentRequest()->headers->get('authorization') : false;

        if ($token) {
            $pos = strpos($token, 'Bearer');
            if (!is_null($pos)) {
                $str = substr($token, 7);
                $this->setToken($str);
            }
        }
    }

    public static function getCacheKey(string $type, $organisation, $id = null): string {
        return $type . $organisation . $id;
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
