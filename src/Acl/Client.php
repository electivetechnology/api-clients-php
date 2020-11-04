<?php

namespace Elective\ApiClients\Acl;

use Elective\ApiClients\Result;
use Elective\ApiClients\ApiClient;
use Elective\ApiClients\Acl\Authorisation\Check;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Elective\ApiClients\Acl\Client
 *
 * @author Kris Rybak <kris.rybak@electivegroup.com>
 */
class Client extends ApiClient
{
    public const ACTION_VIEW        = 'view';
    public const ACTION_CREATE      = 'create';
    public const ACTION_EDIT        = 'edit';
    public const ACTION_DELETE      = 'delete';
    public const ACTION_UNDELETE    = 'undelete';
    public const ACTION_OPERATOR    = 'operator';
    public const ACTION_MASTER      = 'master';
    public const ACTION_OWNER       = 'owner';

    public const ACL_API_URL        = 'https://acl-api.connect.staging.et-ns.net';
    public const PATH_AUTHORISE     = '/v1/authorise';
    public const PATH_GET_ORGANISATION = '/v1/organisations';

    public function __construct(
        HttpClientInterface $client,
        string $aclApiBaseUrl = self::ACL_API_URL,
        bool $isEnabled = true,
        RequestStack $request
    ) {
        $this->setClient($client);
        $this->setIsEnabled($isEnabled);
        $this->setBaseUrl($aclApiBaseUrl);
        $token = $request->getCurrentRequest()->headers->get('authorization');

        if ($token) {
            $pos = strpos($token, 'Bearer ');
            if (!is_null($pos)) {
                $str = substr($token, 7);

                $this->setToken($str);
            }
        }
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

    public function getOrganisationWithToken($organisation, $token)
    {
        // Prepare client options
        $options = [];

        // Set Token for this request
        $options['auth_bearer'] = $token;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION;

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function getOrganisation($organisation)
    {
        return $this->getOrganisationWithToken($this->getToken(), $organisation);
    }
}
