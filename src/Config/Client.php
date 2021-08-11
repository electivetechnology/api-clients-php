<?php

namespace Elective\ApiClients\Config;

use Elective\ApiClients\ApiClient;
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
 * Elective\ApiClients\Config\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    use Cacheable;

    public const CONFIG_API_URL          = 'https://config-api.connect.staging.et-ns.net';
    public const PATH_GET_CHANNELS       = '/v1/channels';
    public const PATH_GET_CHANNEL_TYPE   = '/v1/channel-types';
    public const PATH_GET_CV_COMPLEXITY  = '/v1/candidates/cv-complexity';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::CONFIG_API_URL,
        bool $isEnabled = true,
        RequestStack $request,
        TagAwareCacheInterface $cacheAdapter = null
    ) {
        $this->setClient($client);
        $this->setBaseUrl($configApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
    
        $this->getAuthorisationHeader($request);
    }

    public function getChannelsWithToken($query, $token) {
        // Generate cache key
        $key = 'channels';

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $query;
    
            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getChannels($query)
    {
        return $this->getChannelsWithToken($query, $this->getToken());
    }

    public function getChannelWithToken($channel, $token, $detailed = null) {
        // Generate cache key
        $key = 'channel' . $channel;

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
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $channel . $detailed;
    
            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);

            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getChannel($channel, $detailed = null)
    {
        return $this->getChannelWithToken($channel, $this->getToken(), $detailed);
    }

    public function getChannelTypeWithToken($token) {
        // Generate cache key
        $key = 'channelType';

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNEL_TYPE . '/';
    
            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);

            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getChannelType()
    {
        return $this->getChannelTypeWithToken($this->getToken());
    }

    public function getCvComplexityWithToken($token) {
        // Generate cache key
        $key = 'cvComplexity';

        // Check cache for data
        $data = $this->getCacheItem($key);

        $tags = [$key];

        if (!$data) {
            $options = [];
            
            // Set token for this request
            $options['auth_bearer'] = $token;
            
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_CV_COMPLEXITY . '/';
            
            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
        
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getCvComplexity()
    {
        return $this->getCvComplexityWithToken($this->getToken());
    }
}
