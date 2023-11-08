<?php

namespace Drupal\m_api\Controller;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\m_api\SegnalaMeService;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\wso2_with_jwt\Wso2Connection;
use \Exception;

/**
 * Class SegnalaMeController.
 */
class SegnalaMeController extends MApiControllerBase {


  private $wso2FactoryService;

  /**
   * Default constructor.
   *
   * @param \Drupal\m_api\SegnalaMeService $apiService
   *   The API Service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(SegnalaMeService $apiService, RouteMatchInterface $routeMatch, StateInterface $state) {
    if (is_a($apiService, 'Drupal\m_api\MClientService')) {
      parent::__construct($apiService, $routeMatch, $state);
    }
    else {
      throw new Exception('Error constructing ' . __CLASS__);
    }
    $this->wso2FactoryService = \Drupal::service('wso2_with_jwt.oauth_client');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('m_api.segnalame_client'),
      $container->get('current_route_match'),
      $container->get('state')
    );
  }

  /**
   * Controller nuova segnalazione Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function nuovaSegnalazione(Request $request, string $instituteId, string $sectorId) {
    $subSectors = $this->apiService->recuperaSottoAree($instituteId, $sectorId);

    return [
      '#type'     => 'pattern',
      '#id'       => 'scheda_dettaglio',
      '#attached' => [
        'library'        => [
          'm_api/segnala-me',
        ],
        'drupalSettings' => [
          'm_api' => [
            'token'                   => $this->wso2FactoryService->getAccessToken(),//Wso2Connection::getAccessToken(),
            'endpoints'               => [
              'nuova_segnalazione' => '/it/servizi/messina/segnala-me/api/newReporting',
              'sub_sectors'        => Url::fromRoute('m_api.segnala_me_sottoaree')->toString(),
              'upload_file'        => Url::fromRoute('m_api.segnala_me_carica_file')->toString(),
              'geodecode'          => '/it/servizi/messina/segnala-me/api/geolocate',
            ],
            'id_sector'               => $sectorId,
            'id_institute'            => $instituteId,
            'sub_sectors'             => $subSectors,
            'url_lista_seganalazioni' => Url::fromRoute('m_api.segnala_me_lista_segnalazioni', ['instituteId' => $instituteId])->toString(),
          ],
        ],
      ],
    ];
  }

  /**
   * Controller lista Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function listaSegnalazioni(Request $request, $instituteId) {
    $sectors = $this->apiService->recuperaAree($instituteId);
    $stati = $this->apiService->recuperaStati();

    return [
      'content' => [
        '#type'     => 'pattern',
        '#id'       => 'segnala_me',
        '#attached' => [
          'library'        => [
            'm_api/segnala-me-lista',
          ],
          'drupalSettings' => [
            'm_api' => [
              'idInstitute'             => $instituteId,
              'aree'                    => $sectors,
              'stati'                    => $stati,
              'token'                   => $this->wso2FactoryService->getAccessToken(),//Wso2Connection::getAccessToken(),
              'url_lista_seganalazioni' => Url::fromRoute('m_api.segnala_me_lista_segnalazioni', ['instituteId' => $instituteId])
                ->toString(),
              'endpoints'               => [
                'lista_segnalazioni' => '/it/servizi/messina/segnala-me/api/reportings',
                'sub_sectors'        => Url::fromRoute('m_api.segnala_me_sottoaree')
                  ->toString(),
                'stati'              => Url::fromRoute('m_api.segnala_me_stati')
                  ->toString(),
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller lista Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function dettaglioSegnalazione(string $instituteId, string $id, Request $request) {
    $result = $this->apiService->dettaglioSegnalazione($id);

    return [
      '#type'     => 'container',
      '#attached' => [
        'library'        => [
          'm_api/segnala-me-dettaglio',
        ],
        'drupalSettings' => [
          'm_api' => [
            'token'                   => $this->wso2FactoryService->getAccessToken(),//Wso2Connection::getAccessToken(),
            'url_lista_seganalazioni' => Url::fromRoute('m_api.segnala_me_lista_segnalazioni', ['instituteId' => $instituteId])->toString(),
            'segnalazione_details'    => $result,
          ],
        ],
      ],
    ];
  }

  /**
   * Controller SottoAree Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function recuperaSottoAree(Request $request) {
    $result = $this->apiService->recuperaSottoAree();
    if (is_a($result, 'Exception') && $responseBody = json_decode($result->getResponse()->getBody()->getContents())) {
      return new JsonResponse(
        $responseBody,
        $result->getResponse()->getStatusCode()
      );
    }
    else {
      return new JsonResponse($result);
    }
  }

  /**
   * Controller Stati Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function recuperaStati(Request $request) {
    $result = $this->apiService->recuperaStati();
    if (is_a($result, 'Exception') && $responseBody = json_decode($result->getResponse()
      ->getBody()
      ->getContents())) {
      // ddm($result->getRequest());
      // ddm($responseBody);
      return new JsonResponse(
        $responseBody,
        $result->getResponse()->getStatusCode()
      );
    }
    else {
      return new JsonResponse($result);
    }
  }

  /**
   * Controller Segnalazioni Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function recuperaSegnalazioni(string $page, Request $request) {
    $body   = $request->getContent();
    $result = $this->apiService->recuperaSegnalazioni($page, $body);
    if (is_a($result, 'Exception') && $responseBody = json_decode($result->getResponse()
      ->getBody()
      ->getContents())) {
      // ddm($result->getRequest());
      // ddm($responseBody);
      return new JsonResponse(
        $responseBody,
        $result->getResponse()->getStatusCode()
      );
    }
    else {
      return new JsonResponse($result);
    }
  }

  /**
   * Controller Aggiungi Segnalazione Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function aggiungiSegnalazione(string $id, int $instituteId, int $sectorId, Request $request) {
    $body = $request->getContent();

    if ($data = json_decode($body)) {
      $result = $this->apiService->aggiungiSegnalazione($id, $body, $instituteId, $sectorId);
      if (is_a($result, 'GuzzleHttp\Exception\ClientException')) {
        if ($json = json_decode($result->getResponse()
          ->getBody()
          ->getContents())) {
          $error = ['error' => $json];
        }
        else {
          $error = [
            'error' => $result->getResponse()
              ->getBody()
              ->getContents(),
          ];
        }

        return new JsonResponse($error, $result->getResponse()
          ->getStatusCode());
      }
      if (is_a($result, 'Exception')) {
        $result = ['error' => $result->getMessage()];
      }

      return new JsonResponse($result);
    }

    return new JsonResponse(NULL, 500);
  }

  /**
   * Controller Upload File Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function uploadFile(Request $request) {
    $files = $request->files->all();

    if ($files) {
      $result = $this->apiService->uploadFile($files);
      if (is_a($result, 'GuzzleHttp\Exception\ClientException')) {
        if ($json = json_decode($result->getResponse()
          ->getBody()
          ->getContents())) {
          $error = ['error' => $json];
        }
        else {
          $error = [
            'error' => $result->getResponse()
              ->getBody()
              ->getContents(),
          ];
        }

        return new JsonResponse($error, $result->getResponse()
          ->getStatusCode());
      }
      if (is_a($result, 'Exception')) {
        $result = ['error' => $result->getMessage()];
      }

      return new JsonResponse($result);
    }

    return new JsonResponse(NULL, 500);
  }

  /**
   * Controller GeoDecode Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function geoDecode(string $coords) {
    $result = $this->apiService->geoDecode($coords);

    return new JsonResponse($result);
  }

}
