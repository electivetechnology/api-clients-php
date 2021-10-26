<?php

namespace Elective\ApiClients\Messages;

use Elective\ApiClients\ApiClient;
use Elective\CacheBundle\Utils\CacheTag;
use Elective\FormatterBundle\Traits\{
    Cacheable
};
use Elective\SecurityBundle\Token\TokenDecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Elective\ApiClients\Messages\Client
 *
 * @author Chris Dixon
 */
class Client extends ApiClient
{
    use Cacheable;

    public const MESSAGES_API_URL               = 'https://messages-api.connect.staging.et-ns.net';
    public const PATH_GET_MESSAGES_TEMPLATE     = '/v2/message-templates';
    public const MODEL_NAME_MESSAGE_TEMPLATE    = 'messageTemplate';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::MESSAGES_API_URL,
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

    public function getMessageTemplate($messageTemplate)
    {
        $token = $this->getToken();

        $organisationId = $this->getOrganisationFromToken($token);

        // Generate cache key
        $key  = self::getCacheKey(self::MODEL_NAME_MESSAGE_TEMPLATE, $organisationId, $messageTemplate);

        // Check cache for data
        $data   = $this->getCacheItem($key);

        // Create tags for cache
        $tags = CacheTag::getCacheTags($organisationId, self::MODEL_NAME_MESSAGE_TEMPLATE, $messageTemplate);

        if (!$data) {
            $options = [];

            // Set token for this request
            $options['auth_bearer'] = $token;

            // Create request URL
            $requestUrl = $this->getBaseUrl() . self::PATH_GET_MESSAGES_TEMPLATE . '/' . $messageTemplate;

            // Send request
            $data = $this->handleRequest('GET', $requestUrl, $options);

            if ($data->isSuccessful()) {
                $this->setCacheItem($key, $data, $this->getDefaultLifetime(), $tags);
            }
        }

        return $data;
    }
}
