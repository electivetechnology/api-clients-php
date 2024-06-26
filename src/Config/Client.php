<?php

namespace Elective\ApiClients\Config;

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
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Elective\ApiClients\Config\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    use Cacheable;

    public const CONFIG_API_URL                     = 'https://config-api.connect.staging.et-ns.net';
    public const PATH_GET_CHANNELS                  = '/v1/channels';
    public const PATH_GET_CHANNEL_TYPE              = '/v1/channel-types';
    public const PATH_GET_CV_COMPLEXITY             = '/v1/candidates/cv-complexity';
    public const PATH_GET_ORGANISATION_CONTENT      = '/v1/organisation-contents';
    public const MODEL_NAME_CV_COMPLEXITY           = 'cvComplexity';
    public const MODEL_NAME_CHANNEL                 = 'channel';
    public const MODEL_NAME_CHANNEL_TYPE            = 'channelType';
    public const MODEL_NAME_ORGANISATION_CONTENT    = 'organisationContent';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::CONFIG_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TokenDecoderInterface $tokenDecoder,
        TagAwareCacheInterface $cacheAdapter = null,
        $defaultLifetime = 0
    ) {
        $this->setClient($client);
        $this->setBaseUrl($configApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        $this->tokenDecoder = $tokenDecoder;
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $this->setDefaultLifetime($defaultLifetime);
    
        $this->getAuthorisationHeader($request);
    }

    public function getChannelsWithToken($query, $token): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CHANNEL, $organisationId, $query);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CHANNEL);

        if (!$data) {

            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '?' . $query;
    
            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getChannels($query = null)
    {
        return $this->getChannelsWithToken($query, $this->getToken());
    }

    public function getChannelWithToken($channel, $token, $detailed = null): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);
    
        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CHANNEL, $organisationId, $channel, (bool)$detailed);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CHANNEL, $channel);

        if (!$data) {
            // Check if there are params
            $detailed = isset($detailed) ? '?detailed=' . $detailed : '';
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $channel . $detailed;
    
            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getChannel($channel, $detailed = null)
    {
        return $this->getChannelWithToken($channel, $this->getToken(), $detailed);
    }

    public function getChannelTypeWithToken($token): Result
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CHANNEL_TYPE, $organisationId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CHANNEL_TYPE);

        if (!$data) {
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNEL_TYPE . '/';
    
            $data = $this->handleRequest('GET', $requestUrl, $options);
    
            if ($data->isSuccessful()) {
                // Send request
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getChannelType()
    {
        return $this->getChannelTypeWithToken($this->getToken());
    }

    public function getCvComplexityWithToken($token): Result 
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_CV_COMPLEXITY, $organisationId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_CV_COMPLEXITY);

        if (!$data) {
    
            $options = [];
            
            // Set token for this request
            $options['auth_bearer'] = $token;
            
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CV_COMPLEXITY . '/';
            
            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getCvComplexity()
    {
        return $this->getCvComplexityWithToken($this->getToken());
    }


    public function getByOrganisationContent($organisationId, $contentName)
    {
        // Generate cache key
        $key = self::getCacheKey(self::MODEL_NAME_ORGANISATION_CONTENT, $organisationId, $contentName);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_ORGANISATION_CONTENT, $contentName);

        if (!$data) {            
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_ORGANISATION_CONTENT . 
                '/organisation/' . $organisationId . '/content/' . $contentName;
            
            // Send request
            $data = $this->handleRequest('GET', $requestUrl, []);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }
}
