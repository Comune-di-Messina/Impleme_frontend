<?php

namespace Drupal\m_api;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\SessionManager;
use Drupal\wso2_with_jwt\Wso2Connection;
use Drupal\wso2_with_jwt\Wso2FactoryService;
use Drupal\wso2_with_jwt\AuthType;
use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class MClientService.
 */
class MClientService
{

  const API_RESULT_OK = "OK";
  const API_RESULT_KO = "KO";
  const USER_FIELDS = [
    'birthdate',
    'gender',
    'given_name',
    'family_name',
    'email',
    'address',
    'mobile_phone',
  ];
  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $userPrivateTempstore;

  /**
   * Drupal\Core\Session\SessionManager definition.
   *
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $session_manager;

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
   * @var \Drupal\m_api\Plugin\OpenIDConnectClient\OpenIDConnectWSO2Client
   */
  protected $oidcWSO2Client;

  /**
   * Wso2FactoryService.
   *
   * @var \Drupal\wso2_with_jwt\Wso2FactoryService
   */
  protected $wso2FactoryService;

  /**
   * Google Maps api key.
   *
   * @var string
   */
  protected $gmapsKey;

  /**
   * Constructs a new MClientService object.
   */
  public function __construct(
    PrivateTempStoreFactory $user_private_tempstore,
    ClientFactory $http_client_factory,
    LoggerInterface $logger,
    StateInterface $state,
    ConfigFactory $configFactory,
    Wso2FactoryService $wso2FactoryService,
    SessionManager $session_manager
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
    // Get gmaps configurations.
    $this->gmapsKey = \Drupal::config('geofield_map.settings')->get('gmap_api_key');

    // Get the pluginmanager container.
    $pluginManager = \Drupal::getContainer()->get('plugin.manager.openid_connect_client.processor');
    // Create a oidcWSO2Client plugin's instance.
    $this->oidcWSO2Client = $pluginManager->createInstance('wso2', $configuration);

    $this->wso2FactoryService = $wso2FactoryService;
    $this->session_manager = $session_manager;
  }

  /**
   * DI
   * {@inheritdoc}.
   */
  public static function create(ContainerInterface $container, array $configuration)
  {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('http_client_factory'),
      $container->get('logger.factory'),
      $container->get('state'),
      $container->get('config.factory'),
      $container->get('wso2_with_jwt.oauth_client'),
      $container->get('session_manager')
    );
  }

  /**
   * @deprecated: Excecute an API call with options.
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
   * @deprecated: Provide default HTTP options to use with Guzzle client.
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
        'Content-Type'  => 'application/json',
      ],
      'query' => $query,
    ];
  }

  /**
   * CALL API Tari Contribuenti.
   */
  public function getTariContribuenti()
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery(
        [
          'richiestaCFisPiva' => $this->userConfig->get('CF'),
        ]
      );
      return $this->wso2FactoryService->get('tari/GetContribuenti', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Tari Dettaglio Contribuenti.
   */
  public function getTariContribuentiDettaglio(string $codContribuente)
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery(
        [
          'richiestaCFisPiva' => $this->userConfig->get('CF'),
          'codContribuente' => $codContribuente,
        ]
      );
      return $this->wso2FactoryService->get('tari/GetDettaglioContribuente', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Imu Situazioni.
   */
  public function getImuSituazioni()
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'richiestaCFisPiva' => $this->userConfig->get('CF'),
          'richiestaDammiAnni' => 'S',
        ]
      );
      return $this->wso2FactoryService->get('imu/GetSituazioni', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Imu Dettaglio Situazione.
   */
  public function getImuDettaglioSituazione(string $anno)
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'richiestaCFisPiva' => $this->userConfig->get('CF'),
          'richiestaAnnoImposta' => $anno,
        ]
      );
      return $this->wso2FactoryService->get('imu/GetDettaglioSituazione', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Visure richiesta Aire.
   */
  public function getVisureAire()
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'codiceFiscale' => $this->userConfig->get('CF'),
        ]
      );
      return $this->wso2FactoryService->get('soggetti_demografici/richiestaAire', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Visure richiesta Apr.
   */
  public function getVisureApr()
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'codiceFiscale' => $this->userConfig->get('CF'),
        ]
      );
      return $this->wso2FactoryService->get('soggetti_demografici/richiestaApr', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Visure richiesta Apr con famiglia.
   */
  public function getVisureAprFamiglia()
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'codiceFiscale' => $this->userConfig->get('CF'),
        ]
      );
      return $this->wso2FactoryService->get('soggetti_demografici/richiestaAprConFamiglia', $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API Visure PDF.
   */
  public function getVisurePdf(string $codiceFiscale, string $doc)
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'codiceFiscale' => $codiceFiscale,
        ]
      );
      return $this->wso2FactoryService->get("soggetti_demografici/richiestaPDF$doc", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME PosizioniNonPagate.
   */
  public function getPosizioniNonPagate(
    string $start = NULL,
    string $end = NULL,
    string $idTributo = '',
    string $ente = 'SIF07'
  ) {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'cfPivaDebitore' => $this->userConfig->get('CF'),
          'dataInizioRange' => $start,
          'dataFineRange' => $end,
          'ente' => $ente,
        ]
      );
      if (!empty($idTributo)) {
        $options['query']['idTributo'] = $idTributo;
      }
      return $this->wso2FactoryService->get("pagome/getPosizioniNonPagate", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME PosizioniPagate.
   */
  public function getPosizioniPagate(string $start, string $end, string $idTributo = '', string $ente = 'SIF07')
  {
    try {

      $options = $this->defaultHttpOptionsWithQuery(
        [
          'cfPivaDebitore' => $this->userConfig->get('CF'),
          'dataInizioRange' => $start,
          'dataFineRange' => $end,
          'ente' => $ente,
        ]
      );
      if (!empty($idTributo)) {
        $options['query']['idTributo'] = $idTributo;
      }
      return $this->wso2FactoryService->get("pagome/getPosizioniPagate", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME Ricevute.
   */
  public function getRtById(string $id)
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      return $this->wso2FactoryService->get("pagome/getRtByID/$id", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME Check Ricevute.
   */
  public function verificaPresenzaRtByIuv(string $iuv, string $ente = 'SIF07')
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery(
        [
          'iuv' => $iuv,
          'ente' => $ente,
        ]
      );
      return $this->wso2FactoryService->get("pagome/verificaPresenzaRtByIUV", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME Check Ricevute.
   */
  public function verificaPresenzaRtByCf(string $start, string $end, string $ente = 'SIF07')
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery(
        [
          'cf' => $this->userConfig->get('CF'),
          'dataInizioRange' => $start,
          'dataFineRange' => $end,
          'ente' => $ente,
        ]
      );
      return $this->wso2FactoryService->get("pagome/verificaPresenzaRtByCF", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API PagoME Check Ricevute.
   */
  public function pagamentoPosizioneAttesa(array $params)
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      if (!array_key_exists('cf', $params)) {
        $params['cf'] = $this->userConfig->get('CF');
      }
      $options['json'] = $params;
      return $this->wso2FactoryService->post("pagome/pagamentoPosizioneAttesa", $options, AuthType::BOTH);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Retrieve enti form cache or from API.
   */
  public function getEnti()
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get('pagopa-tributi/v2/enti', $options, AuthType::BOTH);

      $this->logger->info('Refresh dati enti.');
      if (is_a($result, 'Exception')) {
        $this->logger->error('Errore nel caricamento degli enti.');
        return [];
      }
      $this->stateService->set('m_api.enti', $result);
      return $result;
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Retrieve tributi for ente form cache or from API.
   *
   * @param string $codEnte
   *   Codice ente.
   *
   * @return array
   *   A list of tributi.
   */
  public function getTributiEnte(string $codEnte)
  {
    try {
      $tributi = [];
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("pagopa-tributi/v2/enti/$codEnte/tributi", $options, AuthType::BOTH);
      $this->logger->info('Refresh dati tributi per {ente}.', ['ente' => $codEnte]);

      if (is_a($result, 'Exception')) {
        $this->logger->error('Errore nel caricamento degli enti.');
        return [];
      }
      $tributi[$codEnte] = $result;
      $this->stateService->set('m_api.tributi', $tributi);
      return $this->filtraTributi($tributi[$codEnte]);
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  private function filtraTributi($tributi)
  {
    $tributiFiltrati = [];
    foreach ($tributi as $tributo) {
      if ($tributo->spontaneo && isset($tributo->DataAttivazione) && !empty($tributo->DataAttivazione)) {
        array_push($tributiFiltrati, $tributo);
      }
    }
    return $tributiFiltrati;
  }

  /**
   * Fiscal Code of current logged in user.
   *
   * @return string|null
   *   The fiscal code if present.
   */
  public function getCurrentCf()
  {
    return $this->userConfig->get('CF');
  }

  /**
   * Returns user info saved from SPID Auth.
   *
   * @return array
   *   The user info recived.
   */
  public function getUserInfo()
  {
    $info = [];
    foreach (self::USER_FIELDS as $field) {
      $value = $this->userConfig->get($field);
      if ($value != NULL) {
        $info[$field] = $value;
      }
    }
    return $info;
  }

  /**
   * Retrieve tributi for ente form cache or from API.
   *
   * @param string $codEnte
   *   Codice ente.
   * @param string $idTributo
   *   ID tributo.
   *
   * @return array
   *   A list of tributi.
   */
  public function getTariffeTributi(string $codEnte, string $idTributo)
  {
    try {

      if ($tariffe = $this->stateService->get('m_api.tariffe') && isset($tariffe[$codEnte][$idTributo])) {
        return $tariffe[$codEnte][$idTributo];
      }
      $tariffe = $tariffe ?? [];
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("pagopa-tributi/v2/enti/$codEnte/tributi/$idTributo/tariffe", $options, AuthType::BOTH);
      $this->logger->info(
        'Refresh dati tariffe per tributo {tributo} dell\'ente {ente}.',
        [
          'ente' => $codEnte,
          'tributo' => $idTributo,
        ]
      );

      if (is_a($result, 'Exception')) {
        $this->logger->error('Errore nel caricamento delle tariffe.');
        return [];
      }
      $tariffe[$codEnte][$idTributo] = $result;
      $this->stateService->set('m_api.tariffe', $tariffe);
      return $tariffe[$codEnte][$idTributo];
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Request a payment for pagamento spontaneo.
   *
   * @param object $payload
   *   Data payload.
   *
   * @return array
   *   Payment data.
   */
  public function requestPayment(object $payload)
  {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $options['json'] = $payload;
      try {
        $result = $this->wso2FactoryService->post("/newbolite/v2/casefiles/spontaneous", $options, AuthType::BOTH);
      } catch (ClientException $e) {
        $result = $e;
      }

      if (is_a($result, 'Exception')) {
        $message = Psr7\str($result->getResponse());
        $this->logger->error('Errore nella richiesta di pagamento: {message}', ['message' => $message]);
        return (object) [];
      }
      return $result;
    } catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   *
   */
  public function getAccessToken()
  {
    return $this->wso2FactoryService->getAccessToken();
  }

  /**
   *
   */
  public function getJWT()
  {
    return $this->wso2FactoryService->getJWT();
  }

  /**
   *
   */
  protected function logErrorAndLogout($message, $error_message)
  {
    $variables = [
      '@message' => $message,
      '@error_message' => $error_message,
    ];
    $this->logger->error('@message. Details: @error_message', $variables);

    if ($this->session_manager->isStarted()) {
      $this->session_manager->destroy();
    }
    $response = new RedirectResponse('/');
    $response->send();
  }
}
