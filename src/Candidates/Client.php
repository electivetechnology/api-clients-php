<?php

namespace Elective\ApiClients\Candidates;

use Elective\ApiClients\Result;
use Elective\ApiClients\ApiClient;
use Elective\CacheBundle\Utils\CacheTag;
use Elective\FormatterBundle\Traits\{
    Cacheable,
    Outputable,
    Filterable,
    Sortable,
    Loggable
};
use Elective\SecurityBundle\Token\TokenDecoderInterface;
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

    public const CANDIDATE_API_URL              = 'https://candidates-api.connect.staging.et-ns.net';
    public const PATH_GET_CANDIDATE             = '/v1/candidates';
    public const MODEL_NAME_CANDIDATE           = 'candidate';
    public const MODEL_NAME_NUMBER_OF_RECORDS   = 'numberOfRecords';

    public function __construct(
        HttpClientInterface $client,
        string $candidatesApiBaseUrl = self::CANDIDATE_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TokenDecoderInterface $tokenDecoder,
        TagAwareCacheInterface $cacheAdapter = null,
        $defaultLifetime = 0
    ) {
        $this->setClient($client);
        $this->setBaseUrl($candidatesApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        $this->tokenDecoder = $tokenDecoder;
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $this->setDefaultLifetime($defaultLifetime);
    
        $this->getAuthorisationHeader($request);
    }

    public function getCandidateWithToken($candidate, $token, $detailed = null): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CANDIDATE, $organisationId, $candidate);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CANDIDATE, $candidate);

        if (!$data) {
            // Check if there are params
            $detailed = isset($detailed) ? '?detailed=' . $detailed : '';
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $candidate . $detailed;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getCandidate($candidate, $detailed = null)
    {
        return $this->getCandidateWithToken($candidate, $this->getToken(), $detailed);
    }

    public function getCandidatesWithToken($filter, $token): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);
    
        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CANDIDATE, $organisationId, $filter);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CANDIDATE);

        if (!$data) {

            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/' . $filter;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getCandidates($filter)
    {
        return $this->getCandidatesWithToken($filter, $this->getToken());
    }

    public function getNumberOfRecordsWithToken($token): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_NUMBER_OF_RECORDS, $organisationId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_NUMBER_OF_RECORDS);

        if (!$data) {
    
            $options = [];
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/';

            // Send request
            $data = $this->handleRequest('HEAD', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getNumberOfRecords()
    {
        return $this->getNumberOfRecordsWithToken($this->getToken());
    }

    public function getCandidateByVendorWithToken($vendor, $vendorId, $token): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);
    
        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CANDIDATE, $organisationId, $vendor, $vendorId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CANDIDATE);

        if (!$data) {

            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CANDIDATE . '/vendor/' . $vendor . '/' . $vendorId;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getCandidateByVendor($vendor, $vendorId)
    {
        return $this->getCandidateWithToken($vendor, $vendorId, $this->getToken());
    }
}
