<?php

namespace Elective\ApiClients\Candidates;

use Elective\ApiClients\ApiClient;
use Elective\FormatterBundle\Traits\{
    Cacheable,
    Outputable,
    Filterable,
    Sortable,
    Loggable
};
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Elective\ApiClients\Candidates\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    use Cacheable;

    public const CANDIDATE_API_URL       = 'https://candidates-api.connect.staging.et-ns.net';
    public const PATH_GET_CANDIDATE      = '/v1/candidates';

    public function __construct(
        HttpClientInterface $client,
        string $candidatesApiBaseUrl = self::CANDIDATE_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TagAwareCacheInterface $cacheAdapter = null
    ) {
        $this->setClient($client);
        $this->setBaseUrl($candidatesApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $token = $request->getCurrentRequest() ? $request->getCurrentRequest()->headers->get('authorization') : false;

        if ($token) {
            $pos = strpos($token, 'Bearer');
            if (!is_null($pos)) {
                $str =substr($token, 7);

                $this->setToken($str);
            }
        }

    }

    public function getCandidateWithToken($candidate, $token, $detailed = null) {
        // Generate cache key
        $key = 'candidate' . $candidate;

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        $options = [];

        if (!$data) {
            // Check if there are params
            $detailed = isset($detailed) ? '?detailed=' . $detailed : '';
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $candidate . $detailed;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getCandidate($candidate, $detailed = null)
    {
        return $this->getCandidateWithToken($candidate, $this->getToken(), $detailed);
    }

    public function getCandidatesWithToken($filter, $token) {
        // Generate cache key
        $key = 'candidates';

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        $options = [];

        if (!$data) {
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $filter;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getCandidates($filter)
    {
        return $this->getCandidatesWithToken($filter, $this->getToken());
    }

    public function getNumberOfRecordsWithToken($token) {
        // Generate cache key
        $key = 'numberOfRecords';

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        $options = [];

        if (!$data) {
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/';

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('HEAD', $requestUrl, $options);
        }
    }

    public function getNumberOfRecords()
    {
        return $this->getNumberOfRecordsWithToken($this->getToken());
    }
}
