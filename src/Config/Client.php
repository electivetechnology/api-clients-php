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
    use CacheTag;

    public const CONFIG_API_URL          = 'https://config-api.connect.staging.et-ns.net';
    public const PATH_GET_CHANNELS       = '/v1/channels';
    public const PATH_GET_CHANNEL_TYPE   = '/v1/channel-types';
    public const PATH_GET_CV_COMPLEXITY  = '/v1/candidates/cv-complexity';
    public const CV_COMPLEXITY           = 'cvComplexity';
    public const CHANNEL                 = 'channel';
    public const CHANNELS                = 'channels';
    public const CHANNEL_TYPE            = 'channelType';

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
        $organisationId = $this->getTokenDecoder()->getAttribute('organisation')->getValue();

        // Generate cache key
        $key = self::getCacheKey(self::CHANNELS, $organisationId, $query);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::CHANNELS);

        if (!$data) {

            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $query;
    
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
        $organisationId = $this->getTokenDecoder()->getAttribute('organisation')->getValue();
    
        // Generate cache key
        $key = self::getCacheKey(self::CHANNEL, $organisationId, $channel);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::CHANNEL, $channel);

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
        $organisationId = $this->getTokenDecoder()->getAttribute('organisation')->getValue();

        // Generate cache key
        $key = self::getCacheKey(self::CHANNEL_TYPE, $organisationId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::CHANNEL_TYPE);

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
        $organisationId = $this->getTokenDecoder()->getAttribute('organisation')->getValue();

        // Generate cache key
        $key = self::getCacheKey(self::CV_COMPLEXITY, $organisationId);

        // Check cache for data
        $data = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::CV_COMPLEXITY);

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
}
