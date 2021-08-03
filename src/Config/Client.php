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

    public function __construct(
        HttpClientInterface $client,
        string $configApiBaseUrl = self::CONFIG_API_URL,
        RequestStack $request
    ) {
        $this->setClient($client);
        $this->setBaseUrl($configApiBaseUrl);
        $token = $request->getCurrentRequest() ? $request->getCurrentRequest()->headers->get('authorization') : false;

        if ($token) {
            $pos = strpos($token, 'Bearer');
            if (!is_null($pos)) {
                $str =substr($token, 7);

                $this->setToken($str);
            }
        }

    }

    public function getChannelsWithToken($channel, $token, $detailed = null) {
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

    public function getChannels($channel, $detailed = null)
    {
        return $this->getChannelsWithToken($channel, $this->getToken(), $detailed);
    }
}
