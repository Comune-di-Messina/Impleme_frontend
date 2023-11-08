<?php

namespace Drupal\wso2_with_jwt;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use Drupal\wso2_with_jwt\Exceptions\UnableToRefreshTokenException;

/**
 * Class WSO2FactoryService
 *
 * This service represents the factory to manage the authorization and execution of all HTTP requests to the back end.
 */
class WSO2FactoryService
{
    // This is the store for the OAuth tokens.
    static $SPID_TOKENS_STORE_KEY = "SPID_TOKENS";

    /**
     * Drupal\user\PrivateTempStoreFactory definition.
     *
     * @var \Drupal\user\PrivateTempStoreFactory
     */
    protected $userPrivateTempstore;

    /**
     * The HTTP client to fetch the feed data with.
     *
     * @var \Drupal\Core\Http\ClientFactory
     */
    protected $httpClientFactory;

    /**
     * A logger instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * The HTTP Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * WSO2 configuration array.
     *
     * @var array
     */
    protected $userConfig;

    /**
     * StateInterface.
     *
     * @var \Drupal\Core\State\StateInterface
     */
    protected $stateService;

    /**
     * OpenIDConnectWSO2Client.
     *
     * @var \Drupal\wso2_with_jwt\Plugin\OpenIDConnectClient\OpenIDConnectWSO2Client
     */
    protected $oidcWSO2Client;

    /**
     * Constructs a new MClientService object.
     */
    public function __construct(
        PrivateTempStoreFactory $user_private_tempstore,
        ClientFactory $http_client_factory,
        LoggerInterface $logger,
        StateInterface $state,
        ConfigFactory $configFactory
    ) {
        $this->userPrivateTempstore = $user_private_tempstore;
        $this->httpClientFactory = $http_client_factory;
        $this->logger = $logger;
        $this->stateService = $state;
        $this->userConfig = $this->userPrivateTempstore->get('wso2_with_jwt');
        $this->client = $this->httpClientFactory->fromOptions(
            [
                'verify' => FALSE,
                'base_uri' => $configFactory->get('wso2_with_jwt.settings')->get('wso2_with_jwt.base_url'),
                'headers' => [
                    'Content-Type'   => 'application/json',
                ],
            ]
        );

        // Get wso2 configurations.
        $configuration = \Drupal::config('openid_connect.settings.spid')->get('settings');
        // Get the pluginmanager container.
        $pluginManager = \Drupal::getContainer()->get('plugin.manager.openid_connect_client.processor');
        // Create a oidcWSO2Client plugin's instance.
        $this->oidcWSO2Client = $pluginManager->createInstance('spid', $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration)
    {
        return new static(
            $container->get('user.private_tempstore'),
            $container->get('http_client_factory'),
            $container->get('logger.factory'),
            $container->get('state'),
            $container->get('config.factory')
        );
    }

    public function refreshTokens()
    {
        try {
            //Store the refreshed tokens as soon as available
            $this->saveTokens(
                //Invoke the refresh_token routine
                $this->oidcWSO2Client->refreshToken(
                    $this->getRefreshToken()
                )
            );
        } catch (UnableToAuthorizeException $e) {
            throw $e;
        } catch (UnableToRefreshTokenException $e) {
            throw $e;
        }
    }

    public function getFreshAccessToken()
    {
        try {
            if (!$this->isTokenValid()) {
                $this->refreshTokens();
            }
            return $this->getAccessToken();
        } catch (UnableToAuthorizeException $e) {
            throw $e;
        } catch (UnableToRefreshTokenException $e) {
            throw $e;
        }
    }

    private function isTokenValid()
    {
        if (!$this->getJWT()) {
            throw new UnableToAuthorizeException("no id_token found");
            return FALSE;
        }

        if (!$this->getAccessToken()) {
            throw new UnableToAuthorizeException("no access_token found");
            return FALSE;
        }

        if (!$this->getRefreshToken()) {
            throw new UnableToAuthorizeException("no refresh_token found");
            return FALSE;
        }

        if (!$this->getExpire()) {
            throw new UnableToAuthorizeException("no expire found");
            return FALSE;
        }

        // I'll take a 360s buffer in which I'm refreshing an access_token that is about to expire
        return ($this->getExpire() - 360) > time();
    }

    private function isAuthenticated()
    {
        return $this->getJWT() &&
            $this->getAccessToken() &&
            $this->getRefreshToken() &&
            $this->getExpire() &&
            (($this->getExpire() - 360) > time()); // I'll take a 360s buffer in which I'm refreshing an access_token that is about to expire
    }

    /**
     * Excecute a GET API call with options.
     *
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     * @param int $authType
     *   Specify the level of authorization required by the server. Could be:
     *      - AuthType::None: no authorization needed
     *      - AuthType::ACCESS_TOKEN_ONLY: will add the "Authorization: Bearer ..." header
     *      - AuthType::JWT_ONLY: will add the "X-Auth-Token: ..." header alone. Note: this should never be used: use AuthType::BOTH instead.
     *      - AuthType::BOTH: will add both "Authorization: Bearer ..." and "X-Auth-Token: ..."
     *
     * @return mixed|\GuzzleHttp\Psr7\Response
     *   A renderable string or array.
     */
    public function get(string $path, array $options, int $authType = AuthType::NONE)
    {
        try {
            if ($authType == AuthType::BOTH || $authType == AuthType::JWT_ONLY) {
                $options['headers']['X-Auth-Token'] = $this->getJWT();
            }
            if ($authType == AuthType::BOTH || $authType == AuthType::ACCESS_TOKEN_ONLY) {
                $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
            }

            return $this->request('GET', $path, $options);
        } catch (UnableToAuthorizeException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        } catch (ClientException $e) {
            throw $e;
        } catch (ServerException $e) {
            throw $e;
        }
    }

    /**
     * Excecute a POST API call with options.
     *
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     *
     * @return mixed|\GuzzleHttp\Psr7\Response
     *   A renderable string or array.
     */
    public function post(string $path, array $options, int $authType = AuthType::NONE)
    {
        try {
            if ($authType == AuthType::BOTH || $authType == AuthType::JWT_ONLY) {
                $options['headers']['X-Auth-Token'] = $this->getJWT();
            }
            if ($authType == AuthType::BOTH || $authType == AuthType::ACCESS_TOKEN_ONLY) {
                $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
            }
            return $this->request('POST', $path, $options);
        } catch (UnableToAuthorizeException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        } catch (ClientException $e) {
            throw $e;
        } catch (ServerException $e) {
            throw $e;
        }
    }

    /**
     * Excecute a PATCH API call with options.
     *
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     *
     * @return mixed|\GuzzleHttp\Psr7\Response
     *   A renderable string or array.
     */
    public function patch(string $path, array $options, int $authType = AuthType::NONE)
    {
        try {
            if ($authType == AuthType::BOTH || $authType == AuthType::JWT_ONLY) {
                $options['headers']['X-Auth-Token'] = $this->getJWT();
            }
            if ($authType == AuthType::BOTH || $authType == AuthType::ACCESS_TOKEN_ONLY) {
                $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
            }
            return $this->request('PATCH', $path, $options);
        } catch (UnableToAuthorizeException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        } catch (ClientException $e) {
            throw $e;
        } catch (ServerException $e) {
            throw $e;
        }
    }

    /**
     * Excecute a DELETE API call with options.
     *
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     *
     * @return mixed|\GuzzleHttp\Psr7\Response
     *   A renderable string or array.
     */
    public function delete(string $path, array $options, int $authType = AuthType::NONE)
    {
        try {
            if ($authType == AuthType::BOTH || $authType == AuthType::JWT_ONLY) {
                $options['headers']['X-Auth-Token'] = $this->getJWT();
            }
            if ($authType == AuthType::BOTH || $authType == AuthType::ACCESS_TOKEN_ONLY) {
                $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
            }
            return $this->request('DELETE', $path, $options);
        } catch (UnableToAuthorizeException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        } catch (ClientException $e) {
            throw $e;
        } catch (ServerException $e) {
            throw $e;
        }
    }

    /**
     * Excecute an API call with options.
     *
     * @param string $method
     *   HTTP method.
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     *
     * @return mixed|\GuzzleHttp\Psr7\Response|null
     *   A renderable string or array.
     */
    private function request(string $method, string $path, array $options)
    {
        try {
            if (
                (array_key_exists('Authorization', $options['headers']) || array_key_exists('X-Auth-Token', $options['headers'])) &&
                !$this->isTokenValid()
            ) {
                $this->refreshTokens();

                // $options = $this->defaultHttpOptionsWithQuery([]);
                if (array_key_exists('Authorization', $options['headers']) && $this->getAccessToken()) {
                    $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
                }

                if (array_key_exists('X-Auth-Token', $options['headers']) && $this->getJWT()) {
                    $options['headers']['X-Auth-Token'] = $this->getJWT();
                }
            }
        } catch (UnableToAuthorizeException $e) { // Improbabile che venga lanciata, a questo punto
            $this->logger->error($e->getMessage());
            throw $e;
        } catch (UnableToRefreshTokenException $e) {
            // Bubble it up as UnableToAuthorizeException
            $this->logger->error($e->getMessage());
            throw new UnableToAuthorizeException($e->getMessage(), $e->getCode(), $e);
        }

        for ($i = 0; $i < 10; $i++) {
            try {
                $response = $this->client->request($method, $path, $options);
                if ($response->getStatusCode() > 299) {
                    throw new ClientException(
                        $response->getReasonPhrase(),
                        new Request($method, $path, $options['headers'], json_encode($options['json'])),
                        $response
                    );
                }
                if ($response->getStatusCode() == 401) {
                    $this->refreshTokens();

                    // $options = $this->defaultHttpOptionsWithQuery([]);
                    if (array_key_exists('Authorization', $options['headers']) && $this->getAccessToken()) {
                        $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
                    }

                    if (array_key_exists('X-Auth-Token', $options['headers']) && $this->getJWT()) {
                        $options['headers']['X-Auth-Token'] = $this->getJWT();
                    }
                }
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
                    $contents = $response->getBody()->getContents();
                    $decoded = json_decode($contents);
                    if ($decoded !== NULL) {
                        return $decoded;
                    }
                    return $contents;
                }
                throw new ClientException(
                    'Request failed 10 times: aborting.',
                    new Request($method, $path, $options['headers'], json_encode($options['json'])),
                    $response
                );
            } catch (ClientException $e) {
                throw $e;
            } catch (ServerException $e) {
                if ($i == 9) throw $e;
                else {
                    $this->refreshTokens();
                    // $options = $this->defaultHttpOptionsWithQuery([]);
                    if (array_key_exists('Authorization', $options['headers']) && $this->getAccessToken()) {
                        $options['headers']['Authorization'] = sprintf("Bearer %s", $this->getAccessToken());
                    }

                    if (array_key_exists('X-Auth-Token', $options['headers']) && $this->getJWT()) {
                        $options['headers']['X-Auth-Token'] = $this->getJWT();
                    }
                }
            } catch (UnableToRefreshTokenException $e) {
                throw $e;
            }
        }
    }

    /**
     * Excecute an API call with options. Unused.
     *
     * @param string $method
     *   HTTP method.
     * @param string $path
     *   The service endpoint.
     * @param array $options
     *   Array of options to be used with Guzzle client.
     *
     * @return \GuzzleHttp\Psr7\Response
     *   A response object.
     */
    protected function requestBypass(string $method, string $path, array $options)
    {
        return $this->client->request($method, $path, $options);
    }

    /**
     * Provide default HTTP options to use with Guzzle client.
     *
     * @param array $query
     *   Array of query parameters.
     *
     * @return array
     *   Options to be used with Guzzle client.
     */
    protected function defaultHttpOptionsWithQuery(array $query)
    {
        $queryBackup = $query;

        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type'  => 'application/json',
                'X-Auth-Token'  => $this->getJWT(),
            ],
            'query' => $query,
        ];
    }

    /**
     * Perform HTTP request
     */
    public function __call($name, $args)
    {
        return call_user_func_array([$this, $name], $args);
    }

    /**
     * This function stores the tokens to the UserTempStore to further use
     *
     * @param mixed $tokens
     * @return void
     */
    private function saveTokens($tokens)
    {
        $collection = $this->userPrivateTempstore->get('wso2_with_jwt');
        $collection->set(self::$SPID_TOKENS_STORE_KEY, $tokens);

        // // TODO: rimuovere
        // if (is_array($tokens)) {
        //     if ($tokens['id_token']) $collection->set('WSO2_JWT', $tokens['id_token']);
        // } else {
        //     if ($tokens->id_token) $collection->set('WSO2_JWT', $tokens->id_token);
        // }
    }


    /**
     * This function returns tokens stored into the UserTempStore. If no tokens were stored, the function returns FALSE
     *
     * @return mixed|bool
     */
    private function getTokens()
    {
        $collection = $this->userPrivateTempstore->get('wso2_with_jwt');
        $tokens = $collection->get(self::$SPID_TOKENS_STORE_KEY);
        return $tokens ?? FALSE;
    }

    /**
     * This function returns the access_token stored into the UserTempStore. If no tokens were stored, the function returns FALSE
     *
     * @return string|bool
     */
    public function getAccessToken()
    {
        $tokens = $this->getTokens();

        if (!$tokens) return FALSE;

        if (is_array($tokens))
            return $tokens['access_token'] ?? FALSE;
        else
            return $tokens->access_token ?? FALSE;
    }

    /**
     * This function returns the id_token stored into the UserTempStore. If no tokens were stored, the function returns FALSE
     *
     * @return string|bool
     */
    public function getJWT()
    {
        // $collection = $this->userPrivateTempstore->get('wso2_with_jwt');
        // return $collection->get('WSO2_JWT') ?? FALSE;
        $tokens = $this->getTokens();
        if (!$tokens) return FALSE;

        if (is_array($tokens))
            return $tokens['id_token'] ?? FALSE;
        else
            return $tokens->id_token ?? FALSE;
    }

    /**
     * This function returns the refresh_token stored into the UserTempStore. If no tokens were stored, the function returns FALSE
     *
     * @return string|bool
     */
    private function getRefreshToken()
    {
        $tokens = $this->getTokens();

        if (!$tokens) return FALSE;

        if (is_array($tokens))
            return $tokens['refresh_token'] ?? FALSE;
        else
            return $tokens->refresh_token ?? FALSE;
    }

    /**
     * This function returns the LocalDateTime of the expire stored into the UserTempStore. If no tokens were stored, the function returns FALSE
     *
     * @return string|bool
     */
    private function getExpire()
    {
        $tokens = $this->getTokens();

        if (!$tokens) return FALSE;

        if (is_array($tokens))
            return $tokens['expire'] ?? FALSE;
        else
            return $tokens->expire ?? FALSE;
    }
}
