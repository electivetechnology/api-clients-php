<?php

namespace Elective\ApiClients\Acl;

use Elective\ApiClients\Result;
use Elective\ApiClients\ApiClient;
use Elective\ApiClients\Acl\Authorisation\Check;
use Elective\FormatterBundle\Traits\{
    Cacheable,
    Outputable,
    Filterable,
    Sortable,
    Loggable
};
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Elective\ApiClients\Acl\Client
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class Client extends ApiClient
{
    use Cacheable;

    public const ACL_API_URL            = 'https://acl-api.connect.staging.et-ns.net';
    public const PATH_AUTHORISE         = '/v1/authorise';
    public const PATH_GET_ORGANISATION  = '/v1/organisations';
    public const PATH_TOKEN_EXCHANGE    = '/v1/token/exchange';
    public const PATH_GET_USERS         = '/v1/users';
    public const PATH_ROLE              = '/v1/roles';

    public function __construct(
        HttpClientInterface $client,
        string $aclApiBaseUrl = self::ACL_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TagAwareCacheInterface $cacheAdapter = null
    ) {
        $this->setClient($client);
        $this->setIsEnabled($isEnabled);
        $this->setBaseUrl($aclApiBaseUrl);
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $this->getAuthorisationHeader($request);
    }

    public function isTokenAuthorised($token, Check $check, array $checks = []): Result
    {
        // Prepare client options
        $options = [];

        // Set data
        $options['json'] = $check;

        // Set Token for this request
        $options['auth_bearer'] = $token;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_AUTHORISE;

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    public function isAuthorised(Check $check, array $checks = [])
    {
        return $this->isTokenAuthorised($this->getToken(), $check, $checks);
    }

    public function getOrganisationWithToken($organisation, $token, $detailed = null)
    {
        // Generate cache key
        $key = 'organisation' . $organisation;

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
            $detailed = isset($detailed) ? '?detailed=' . $detailed : '';
            // Prepare client options
            $options = [];

            // Set Token for this request
            $options['auth_bearer'] = $token;

            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION . '/' . $organisation . $detailed;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);

            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getOrganisation($organisation, $detailed = null)
    {
        return $this->getOrganisationWithToken($organisation, $this->getToken(), $detailed);
    }

    /**
     * Exchanges token for a new one of chosen Organisation
     *
     * @return Result
     */
    public function exchangeToken($token, $organisation, $extended = false): ?Result
    {
        $payload = new \StdClass();
        $payload->organisation = $organisation;
        $payload->extended = $extended;

        // Prepare client options
        $options = [];

        // Set Token for this request
        $options['auth_bearer'] = $token;

        // Set data
        $options['json'] = $payload;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_TOKEN_EXCHANGE;

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    public function exchangeCurrentToken($organisation, $extended = null)
    {
        return $this->exchangeToken($this->getToken(), $organisation, $extended);
    }

    public function getServiceAccount($organisation, $id)
    {
        // Prepare client options
        $options = [];

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION .
            '/' . $organisation . '/service-accounts/' .  $id;

        $options['auth_bearer'] = $this->getToken();

        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function createServiceAccount($organisation, $name, $description = '', $detailed = null, $createToken = null, $role = null)
    {
        $query = '?';
        $query .= isset($detailed) ? '&detailed=' . $detailed : '';
        $query .= isset($createToken) ? '&create-token=' . $createToken : '';
        // Prepare client options
        $options = [];

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION . '/' . $organisation . '/service-accounts' . $query;

        $options['auth_bearer'] = $this->getToken();

        $options['body'] = json_encode(array("name"  => $name, "description"  => $description, "role"  => $role));

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    public function grantServiceAccountRole($id, $organisation, $role)
    {
        // Prepare client options
        $options = [];

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION .
            '/' . $organisation . '/service-accounts/' .  $id . '/grant';

        $options['auth_bearer'] = $this->getToken();

        $options['body'] = json_encode(array("role" => $role));

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }

    public function getUserDetails($username)
    {
        $filter = '?filters[]=and-user.username-eq-value-' . $username;
        
        // Prepare client options
        $options = [];
        
        // Set Token for this request
        $options['auth_bearer'] = $this->getToken();
        
        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_USERS . $filter;

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function readRole($spec)
    {
        // Prepare client options
        $options = [];

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_ROLE . '/' . $spec;

        $options['auth_bearer'] = $this->getToken();

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function createRole($name, $description = '', $permissionGroups = [], $type = false)
    {
        // Prepare client options
        $options = [];

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_ROLE;

        $options['auth_bearer'] = $this->getToken();

        $body = array("name" => $name, "description" => $description, "permissionGroups" => $permissionGroups);

        if ($type) {
            $body['type'] = $type;
        }

        $options['body'] = json_encode($body);

        // Send request
        return $this->handleRequest('POST', $requestUrl, $options);
    }
}
