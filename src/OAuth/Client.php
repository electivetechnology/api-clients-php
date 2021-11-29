<?php

namespace Elective\ApiClients\OAuth;

use Elective\ApiClients\Result;
use Elective\ApiClients\ApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Elective\ApiClients\OAuth\Client
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class Client extends ApiClient
{
    public const OAUTH_URL              = 'https://oauth.connect.staging.et-ns.net';
    public const PATH_AUTHORIZATIONS    = '/v1/oauth2/authorizations';

    public function __construct(
        HttpClientInterface $client,
        string $oauthBaseUrl = self::OAUTH_URL,
        bool $isEnabled = true,
        RequestStack $request
    ) {
        $this->setClient($client);
        $this->setIsEnabled($isEnabled);
        $this->setBaseUrl($oauthBaseUrl);
        $token = $request->getCurrentRequest() ? $request->getCurrentRequest()->headers->get('authorization') : false;

        if ($token) {
            $pos = strpos($token, 'Bearer ');
            if (!is_null($pos)) {
                $str = substr($token, 7);

                $this->setToken($str);
            }
        }
    }

    /**
     * Create new Authorization
     *
     * @return Result
     */
    public function createAuthorization($clientId, $vendor, $scopes = null, $username = null, $secret = null, $state = null): ?Result
    {
        $payload = new \StdClass();
        $payload->clientId = $clientId;
        $payload->vendor = $vendor;
        $payload->scopes = $scopes;
        $payload->username = $username;
        $payload->clientSecret = $secret;
        $payload->state = $state;

        // Prepare client options
        $options = [];

        // Set Token for this request
        $options['auth_bearer'] = $this->getToken();

        // Set data
        $options['json'] = $payload;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_AUTHORIZATIONS;

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    /**
     * Create new Authorization
     *
     * @return Result
     */
    public function reAuthorization($componentId): ?Result
    {
        // Prepare client options
        $options = [];

        // Set Token for this request
        $options['auth_bearer'] = $this->getToken();

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_AUTHORIZATIONS . '/' . $componentId . '/token';

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }
}
