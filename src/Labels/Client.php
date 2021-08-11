<?php

namespace Elective\ApiClients\Labels;

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
 * Elective\ApiClients\Labels\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    use Cacheable;

    public const LABELS_API_URL       = 'https://labels-api.connect.staging.et-ns.net';
    public const PATH_GET_LABELS      = '/v1/labels';
    public const LABELS               = 'labels';
    public const LABEL                = 'label';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::LABELS_API_URL,
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

    public function getLabelWithToken($label, $token) {
        // Generate cache key
        $key  = $this->getCacheKey(self::LABEL, $label);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        $tags   = [$key];

        if (!$data) {
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_LABELS . '/' . $label;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getLabel($label)
    {
        return $this->getLabelWithToken($label, $this->getToken());
    }

    public function getLabelsWithToken($filter, $token) {
        // Generate cache key
        $key  = $this->getCacheKey(self::LABELS);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        $tags   = [$key];
    
        if (!$data) {
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_LABELS . '/' . $filter;

            $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
    
            // Send request
            return $this->handleRequest('GET', $requestUrl, $options);
        }
    }

    public function getLabels($label)
    {
        return $this->getLabelsWithToken($label, $this->getToken());
    }
}
