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
    public const CANDIDATE               = 'candidate';
    public const CANDIDATES              = 'candidates';
    public const NUMBER_OF_RECORDS       = 'numberOfRecords';

    public function __construct(
        HttpClientInterface $client,
        string $candidatesApiBaseUrl = self::CANDIDATE_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TagAwareCacheInterface $cacheAdapter = null,
        $defaultLifetime = 0
    ) {
        $this->setClient($client);
        $this->setBaseUrl($candidatesApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $this->setDefaultLifetime($defaultLifetime);
    
        $this->getAuthorisationHeader($request);
    }

    public function getCandidateWithToken($candidate, $token, $detailed = null) {
        // Generate cache key
        $key = self::getCacheKey(self::CANDIDATE, $candidate);

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
            // Check if there are params
            $detailed = isset($detailed) ? '?detailed=' . $detailed : '';
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $candidate . $detailed;

            // Send request
            $ret = $this->handleRequest('GET', $requestUrl, $options);

            $data = $ret;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            return $ret;
        }
        return $data;
    }

    public function getCandidate($candidate, $detailed = null)
    {
        return $this->getCandidateWithToken($candidate, $this->getToken(), $detailed);
    }

    public function getCandidatesWithToken($filter, $token) {
        // Generate cache key
        $key = self::getCacheKey(self::CANDIDATES);

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {

            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $filter;

            // Send request
            $ret = $this->handleRequest('GET', $requestUrl, $options);

            $data = $ret;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);

            return $ret;
        }

        return $data;
    }

    public function getCandidates($filter)
    {
        return $this->getCandidatesWithToken($filter, $this->getToken());
    }

    public function getNumberOfRecordsWithToken($token) {
        // Generate cache key
        $key = self::getCacheKey(self::NUMBER_OF_RECORDS);

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
    
            $options = [];
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/';

            // Send request
            $ret = $this->handleRequest('HEAD', $requestUrl, $options);

            $data = $ret;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            
            return $ret;
        }

        return $data;
    }

    public function getNumberOfRecords()
    {
        return $this->getNumberOfRecordsWithToken($this->getToken());
    }
}
