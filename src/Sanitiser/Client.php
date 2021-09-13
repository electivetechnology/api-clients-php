<?php

namespace Elective\ApiClients\Sanitiser;

use Elective\ApiClients\Result;
use Elective\ApiClients\ApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Elective\ApiClients\Sanitiser\Client
 *
 * @author Chris Dixon
 */
class Client extends ApiClient
{
    public const SANITISER_API_URL              = 'https://sanitiser-api.connect.staging.et-ns.net';
    public const PATH_GET_EMAIL                = '/v1/email';
    public const PATH_GET_PHONE                = '/v1/phone';

    public function __construct(
        HttpClientInterface $client,
        string $sanitiserApiBaseUrl = self::SANITISER_API_URL,
        bool $isEnabled = true
    ) {
        $this->setClient($client);
        $this->setBaseUrl($sanitiserApiBaseUrl);
        $this->setIsEnabled($isEnabled);
    }

    public function getEmail($id, $email): Result
    {
        $payload = new \StdClass();
        $payload->uuid = $id;
        $payload->input = $email;

        // Prepare client options
        $options = [];

        // Set data
        $options['json'] = $payload;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_EMAIL;

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    public function getPhone($id, $phone): Result
    {
        $payload = new \StdClass();
        $payload->uuid = $id;
        $payload->input = $phone;

        // Prepare client options
        $options = [];

        // Set data
        $options['json'] = $payload;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_PHONE;

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }
}
