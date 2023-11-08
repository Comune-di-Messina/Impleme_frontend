<?php

namespace Drupal\m_api\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\m_api\PrenotaUfficioService;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PrenotaUfficioController.
 */
class PrenotaUfficioController extends MApiControllerBase {

  /**
   * Default constructor.
   *
   * @param \Drupal\m_api\PrenotaUfficioService $apiService
   *   The API Service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(PrenotaUfficioService $apiService, RouteMatchInterface $routeMatch, StateInterface $state) {
    if (is_a($apiService, 'Drupal\m_api\MClientService')) {
      parent::__construct($apiService, $routeMatch, $state);
    }
    else {
      throw new Exception('Error constructing ' . __CLASS__);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('m_api.prenotaufficio_client'),
      $container->get('current_route_match'),
      $container->get('state')
    );
  }

  /**
   * Controller lista uffici.
   *
   * @param string $municipalityName
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioOfficesList(string $municipalityName) {
    $municipalityId     = _m_core_get_municipality_code_from_name($municipalityName);
    $serviceTypes       = $this->apiService->getServiceTypes($municipalityId);
    $currentServiceType = count($serviceTypes) ? $serviceTypes[0] : FALSE;

    return [
      '#type'     => 'container',
      '#attached' => [
        'library'        => [
          'm_api/prenota-ufficio-offices-list',
        ],
        'drupalSettings' => [
          'm_api' => [
        // Wso2Connection::getAccessToken(),
            'token'     => $this->apiService->getAccessToken(),
            'endpoints' => [
              'officesList'        => Url::fromRoute('m_api.prenota_ufficio.api-lista-uffici-filtro', [
                'municipalityName' => $municipalityName,
              ])->toString(),
              'publicServicesList' => '/it/servizi/messina/prenota-ufficio/api/publicServices',
            ],
            'data'      => [
              'serviceTypes'   => $serviceTypes,
              'publicServices' => $this->apiService->getPublicServices($currentServiceType['id']),
              'officesList'    => $this->apiService->getOfficesList($municipalityId, $currentServiceType['id']),
              'ente'           => $this->ente,
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller lista uffici filtrata.
   *
   * @param string $municipalityName
   *   Description.
   * @param string|null $serviceType
   *   Description.
   * @param string|null $serviceId
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioFilteredOfficesList(string $municipalityName, string $serviceType = NULL, string $serviceId = NULL) {
    $municipalityId = _m_core_get_municipality_code_from_name($municipalityName);
    $result = $this->apiService->getOfficesList($municipalityId, $serviceType, $serviceId);

    return new JsonResponse($result);
  }

  /**
   * Controller lista publicServices.
   *
   * @param string $serviceType
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioPublicServicesList(string $serviceType) {
    $result = $this->apiService->getPublicServices($serviceType);

    return new JsonResponse($result);
  }

  /**
   * Controller form nuova prenotazione.
   *
   * @param string $municipalityName
   *   Municipality name.
   * @param string $officeId
   *   Description.
   * @param string|null $serviceType
   *   Description.
   * @param string|null $publicService
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioForm(string $municipalityName, string $officeId, string $serviceType = NULL, string $publicService = NULL) {
    $municipalityId       = _m_core_get_municipality_code_from_name($municipalityName);
    $serviceTypes         = $this->apiService->getServiceTypes($municipalityId);
    $currentServiceType   = array_values(array_filter($serviceTypes, static function ($service) use ($serviceType) {
      return $service['id'] === $serviceType;
    }))[0];
    $publicServices       = $this->apiService->getPublicServices($serviceType, $officeId);
    $currentPublicService = $publicService ? array_values(array_filter($publicServices, static function ($service) use ($publicService) {
      return $service['id'] === $publicService;
    }))[0] : FALSE;

    $officeDetails = $this->apiService->getOfficeDetails($municipalityId, $officeId);
    $build = $this->getOpenIdConnectForm();

    return [
      'header' => [
        '#type' => 'pattern',
        '#id' => 'hero_simple',
        '#fields' => [
          'title' => $officeDetails['name'],
          'breadcrumbs' => $this->getBreadcrumb(),
          'content' => $officeDetails['description'],
          'image' => '<img src="' . $officeDetails['imageUrl'] . '">',
          'back' => '<a href="javascript:history.back()">' . t('Torna indietro') . '</a>',
          'extraClasses' => ['prenota-ufficio'],
        ],
      ],
      'vue' => [
        '#type'     => 'container',
        '#attached' => [
          'library'        => [
            'm_api/prenota-ufficio-form',
          ],
          'drupalSettings' => [
            'm_api' => [
          // Wso2Connection::getAccessToken(),
              'token'     => $this->apiService->getAccessToken(),
              'routes'    => [
                'downloadUrl'         => '#',
                'mainUrl'             => '/it/servizi/messina',
                'reservationsListUrl' => Url::fromRoute('m_api.prenota_ufficio.lista-prenotazioni', ['municipalityName' => $municipalityName])
                  ->toString(),
              ],
              'endpoints' => [
                'calendar'       => Url::fromRoute('m_api.prenota_ufficio.api-calendario', [
                  'municipalityName' => $municipalityName,
                ])
                  ->toString(),
                'addReservation' => Url::fromRoute('m_api.prenota_ufficio.api-nuova-prenotazione')
                  ->toString(),
              ],
              'data'      => [
                'servicesTypes'        => $serviceTypes,
                'publicServices'       => $publicServices,
                'currentServiceType'   => $currentServiceType,
                'currentPublicService' => $currentPublicService,
                'office'               => $officeDetails,
                'municipalityId'       => $municipalityId,
                'officeId'             => $officeId,
                'ente'                 => $this->ente,
                'userinfo'             => $this->apiService->getUserInfo() +
                  ['cf' => $this->apiService->getCurrentCf()],
                'isAnonymous'          => \Drupal::currentUser()->isAnonymous(),
                'openIdConnect'      => (isset($build['field_url_servizio'])) ? \Drupal::service('renderer')->renderRoot($build) : NULL,
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller lista prenotazioni.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioReservationsList(Request $request, string $municipalityName) {
    $municipalityId = _m_core_get_municipality_code_from_name($municipalityName);

    $reservationsList = $this->apiService->getReservationsList($municipalityId, $this->apiService->getCurrentCf());

    return [
      'header' => [
        '#type' => 'pattern',
        '#id' => 'hero_simple',
        '#fields' => [
          'title' => 'I tuoi appuntamenti',
          'breadcrumbs' => $this->getBreadcrumb(),
          'back' => '<a href="javascript:history.back()">' . t('Torna indietro') . '</a>',
          'cta_right' => [
            '#markup' => "<a href='/servizi/$municipalityName/prenota-ufficio' class='btn btn-primary'>Effettua nuova prenotazione</a>",
          ],
          'extraClasses' => ['prenota-ufficio-prenotazioni'],
        ],
      ],
      'vue' => [
        '#type'     => 'container',
        '#attached' => [
          'library'        => [
            'm_api/prenota-ufficio-reservations-list',
          ],
          'drupalSettings' => [
            'm_api' => [
          // Wso2Connection::getAccessToken(),
              'token'     => $this->apiService->getAccessToken(),
              'endpoints' => [
                'cancelRequest' => '/it/servizi/messina/prenota-ufficio/api/reservation',
                'reservationsList' => Url::fromRoute('m_api.prenota_ufficio.api-lista-prenotazioni')->toString(),
              ],
              'data'      => [
                'reservationsList' => $reservationsList,
                'userinfo'         => $this->apiService->getUserInfo() +
                  ['cf' => $this->apiService->getCurrentCf()],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller lista prenotazioni.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioGetReservationsList() {
    $reservationsList = $this->apiService->getReservationsList('SIF07', $this->apiService->getCurrentCf());

    return new JsonResponse($reservationsList);
  }

  /**
   * Controller calendario.
   *
   * @param \Drupal\m_api\Controller\Request $request
   *   Description.
   * @param string $municipalityName
   *   Municipality name.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioCalendar(Request $request, string $municipalityName) {
    $response = $this->apiService->getOfficeCalendar($request->query);

    return new JsonResponse($response);
  }

  /**
   * Controller aggiungi prenotazione.
   *
   * @param \Drupal\m_api\Controller\Request $request
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioAddReservation(Request $request) {
    $body = $request->getContent();
    if (json_decode($body)) {
      $response = $this->apiService->addReservation($body);
    }

    return new JsonResponse($response);
  }

  /**
   * Controller cancella prenotazione.
   *
   * @param \Drupal\m_api\Controller\Request $request
   *   Description.
   * @param string $reservationId
   *   Description.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaUfficioCancelReservation(Request $request, string $reservationId) {
    $body = $request->getContent();
    if (json_decode($body)) {
      $response = $this->apiService->clearReservation($reservationId, $body);
    }

    return new JsonResponse($response);
  }

  /**
   * Controller render Open Id Form.
   *
   * @return array
   *   Return a string or a renderable array.
   */
  private function getOpenIdConnectForm(): array {
    $build['#cache']['contexts'][] = 'user.roles:authenticated';
    $currentUser = \Drupal::currentUser();
    $logged_in = $currentUser->isAuthenticated() && \Drupal::service("wso2_with_jwt.oauth_client")->isAuthenticated();
    $claim = new FormattableMarkup('<h3 class="mb-4">@text</h3>', [
      '@text' => t("Effettua l'accesso per accedere al servizio"),
    ]);

    if (!$logged_in) {
      $form = \Drupal::formBuilder()
        ->getForm('Drupal\openid_connect\Form\OpenIDConnectLoginForm');

      $build['field_url_servizio'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ["openid-connect-login"],
        ],
        'claim' => [
          '#markup' => $claim,
        ],
        'element-content' => $form,
        '#weight' => $build['field_url_servizio']['#weight'],
      ];
    }
    return $build;
  }

}
