<?php

namespace Elective\ApiClients\Labels;

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
 * Elective\ApiClients\Labels\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    use Cacheable;

    public const LABELS_API_URL       = 'https://labels-api.connect.staging.et-ns.net';
    public const PATH_GET_LABELS      = '/v1/labels';
    public const MODEL_NAME_LABEL     = 'label';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::LABELS_API_URL,
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

    public function getLabelWithToken($label, $token): Result 
    {
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key  = self::getCacheKey(self::MODEL_NAME_LABEL, $organisationId, $label);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_LABEL, $label);

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
        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key  = self::getCacheKey(self::MODEL_NAME_LABEL, $organisationId, $filter);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_LABEL);

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
