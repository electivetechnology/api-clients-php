<?php

namespace Elective\ApiClients\Labels;

use Elective\ApiClients\Result;
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
        TagAwareCacheInterface $cacheAdapter = null,
        $defaultLifetime = 0
    ) {
        $this->setClient($client);
        $this->setBaseUrl($configApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        if ($cacheAdapter) {
            $this->setCacheAdapter($cacheAdapter);
        };
        $this->setDefaultLifetime($defaultLifetime);

        $this->getAuthorisationHeader($request);
    }

    public function getLabelWithToken($label, $token): Result 
    {
        // Generate cache key
        $key  = self::getCacheKey(self::LABEL, $label);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        $tags   = [$key];

        if (!$data) {
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_LABELS . '/' . $label;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getLabel($label)
    {
        return $this->getLabelWithToken($label, $this->getToken());
    }

    public function getLabelsWithToken($filter, $token): Result 
    {
        // TODO
        $organisation = $this->getTokenDecoder()->getAttribute('organisation');

        // Generate cache key
        $key  = self::getCacheKey(self::LABELS . $filter);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        $tags   = [$key];

        if (!$data) {
    
            $options = [];
    
            // Set token for this request
            $options['auth_bearer'] = $token;
    
            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_LABELS . '/' . $filter;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }

    public function getLabels($label)
    {
        return $this->getLabelsWithToken($label, $this->getToken());
    }
}
