<?php

namespace Elective\ApiClients\Config;

use Elective\ApiClients\ApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Elective\ApiClients\Config\Client
 *
 * @author Sammy Ha
 */
class Client extends ApiClient
{
    public const ACTION_VIEW        = 'view';
    public const ACTION_CREATE      = 'create';
    public const ACTION_EDIT        = 'edit';
    public const ACTION_DELETE      = 'delete';
    public const ACTION_UNDELETE    = 'undelete';
    public const ACTION_OPERATOR    = 'operator';
    public const ACTION_MASTER      = 'master';
    public const ACTION_OWNER       = 'owner';

    public const CONFIG_API_URL     = 'https://config-api.connect.staging.et-ns.net';
    public const PATH_GET_CHANNELS  = '/v1/channels';
    public const PATH_GET_CHANNEL_TYPE  = '/v1/channel-types';

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::CONFIG_API_URL,
        bool $isEnabled = true,
        RequestStack $request
    ) {
        $this->setClient($client);
        $this->setBaseUrl($configApiBaseUrl);
        $this->setIsEnabled($isEnabled);
        $token = $request->getCurrentRequest() ? $request->getCurrentRequest()->headers->get('authorization') : false;

        if ($token) {
            $pos = strpos($token, 'Bearer');
            if (!is_null($pos)) {
                $str =substr($token, 7);

                $this->setToken($str);
            }
        }

    }

    public function getChannelsWithToken($query, $token) {
        $options = [];

        // Set token for this request
        $options['auth_bearer'] = $token;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $query;

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function getChannels($query)
    {
        return $this->getChannelsWithToken($query, $this->getToken());
    }

    public function getChannelWithToken($channel, $token, $detailed = null) {
        // Check if there are params
        $detailed = isset($detailed) ? '?detailed=' . $detailed : '';

        $options = [];

        // Set token for this request
        $options['auth_bearer'] = $token;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNELS . '/' . $channel . $detailed;

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function getChannel($channel, $detailed = null)
    {
        return $this->getChannelWithToken($channel, $this->getToken(), $detailed);
    }

    public function getChannelTypeWithToken($token) {
        $options = [];

        // Set token for this request
        $options['auth_bearer'] = $token;

        // Create request URL
        $requestUrl = $this->getBaseUrl() . self::PATH_GET_CHANNEL_TYPE . '/';

        // Send request
        return $this->handleRequest('GET', $requestUrl, $options);
    }

    public function getChannelType()
    {
        return $this->getChannelTypeWithToken($this->getToken());
    }
}
