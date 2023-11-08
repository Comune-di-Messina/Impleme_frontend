<?php

namespace Drupal\casefiles\Controller;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\casefiles\CasefilesService;
use Drupal\Core\Url;
use Drupal\m_api\Controller\MApiControllerBase;
use GuzzleHttp\Exception\ClientException;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Zumba\GastonJS\Exception\ClientError;

/**
 * Class CasefilesController.
 */
class CasefilesController extends MApiControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(CasefilesService $apiService, RouteMatchInterface $routeMatch, StateInterface $state) {
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
          $container->get('casefiles.casefiles_client'),
          $container->get('current_route_match'),
          $container->get('state')
      );
  }

  /**
   * Elenco degli stati pratia disponibili.
   *
   * @return string|array
   */
  public function listaStati(Request $request) {
    $stati = $this->stateService->get('m_api.prenotame.stati');
    return new JsonResponse($stati, 200);
  }

  /**
   * Elenco pratiche passi carrabili.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function listaPratiche(Request $request) {
    $end = $request->query->get('end');
    $enteId = $request->query->get('enteId');
    $start = $request->query->get('start');
    $stato = $request->query->get('stato');

    $result = $this->apiService->getListaPratiche($end, $enteId, $start, $stato);
    if (is_array($result) && !empty($result)) {
      $stati = $this->stateService->get('m_api.prenotame.stati');
      foreach ($result as $pratica) {
        foreach ($stati as $stato) {
          if ($stato->id == $pratica->state) {
            $pratica->state = $stato;
            break;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Dettagli pratica.
   *
   * @param string $casefileId
   *   Casefile id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Returns.
   */
  public function dettaglioPratica(string $casefileId) {
    $result = $this->apiService->getPratica($casefileId);
    $stati = $this->stateService->get('m_api.prenotame.stati');

    foreach ($stati as $stato) {
      if ($stato->id == $result->state) {
        $result->state = $stato;
        break;
      }
    }

    return $result;
  }

  /**
   * Crea pratica.
   */
  public function creaPratica(Request $request) {
    try {
      $pratica = $this->apiService->postPratica($request);
      return new JsonResponse($pratica);
    }
    catch (ClientException $e) {
      return new JsonResponse([
        'code' => $e->getCode(),
        'error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Restituisce allegato.
   *
   * @param string $casefileId
   *   Casefile Id.
   * @param string $documentId
   *   Document Id.
   *
   * @return mixed
   *   Return.
   */
  public function getAllegato(string $casefileId, string $documentId) {
    return $this->apiService->getAllegato($documentId);
  }

  /**
   * Allega file alla pratica.
   *
   * @param Request $request
   *   Request.
   * @param string $casefileId
   *   Document id.
   *
   * @return mixed
   *   Response.
   */
  public function postAllegato(Request $request, string $casefileId) {
    return $this->apiService->postAllegato($request, $casefileId);
  }

  /**
   * Tiopologie pratica.
   */
  public function listaTipologie(Request $request) {
    $result = $this->apiService->getTipologiePratica();
    return new JsonResponse($result, 200);
  }

  public function listaEnti(Request $request) {
    return new JsonResponse($this->apiService->listaEnti(), 200);
  }

  /**
   * Render lista pratiche.
   *
   * @return array[]
   *   Rendered response.
   */
  public function renderListaPratiche(Request $request) {
    $pratiche = $this->listaPratiche($request);

    return [
      'content' => [
        '#type'     => 'pattern',
        '#id'       => 'pratiche',
        '#attached' => [
          'library'        => [
            'm_api/pratiche-lista',
          ],
          'drupalSettings' => [
            'm_api' => [
              'lista_pratiche'  => $pratiche,
              'tipologie' => $this->apiService->getTipologiePratica(),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Render dettaglio pratica.
   *
   * @return array[]
   *   Rendered response.
   */
  public function renderDettaglioPratica(Request $request, string $casefileId) {
    $pratica = $this->dettaglioPratica($casefileId);

    return [
      'content' => [
        '#type'     => 'pattern',
        '#id'       => 'dettaglio',
        '#attached' => [
          'library'        => [
            'm_api/pratiche-dettaglio',
          ],
          'drupalSettings' => [
            'm_api' => [
              'casefile'  => $pratica,
              'tipologie' => $this->apiService->getTipologiePratica(),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller nuova segnalazione Segnala ME.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function renderNuovaPratica(Request $request) {

    return [
      '#type'     => 'pattern',
      '#id'       => 'nuova',
      '#attached' => [
        'library'        => [
          'm_api/pratiche-nuova',
        ],
        'drupalSettings' => [
          'm_api' => [
            'userinfo' => $this->apiService->getUserInfo() + ['cf' => $this->apiService->getCurrentCf()],
            'tipologie' => $this->apiService->getTipologiePratica(),
          ],
        ],
      ],
    ];
  }

}
