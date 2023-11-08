<?php

namespace Drupal\m_api\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\m_api\PrenotaMeService;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

/**
 * Class PrenotaMeController.
 */
class PrenotaMeController extends MApiControllerBase {

  /**
   * Default constructor.
   *
   * @param \Drupal\m_api\PrenotaMeService $apiService
   *   The API Service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(PrenotaMeService $apiService, RouteMatchInterface $routeMatch, StateInterface $state) {
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
      $container->get('m_api.prenotame_client'),
      $container->get('current_route_match'),
      $container->get('state')
    );
  }

  /**
   * Controller Le tue prenotazioni.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotazioni(Request $request) {
    $stato    = $request->query->get('stato');
    $data     = $request->query->get('data');
    $elements = [
      'data'  => $data,
      'stato' => $stato,
    ];
    $result   = $this->apiService->getPrenotazioni();
    if ($data) {
      $this->filterData($result, 'data', $data);
    }
    if ($stato) {
      $this->filterData($result, 'stato', $stato);
    }
    if (is_a($result, 'Exception')) {
      return parent::render($result->getMessage());
    }
    if (is_object($result) && property_exists($result, 'esito') && property_exists($result, 'descrizione')) {
      return parent::render($result->descrizione);
    }

    if (is_array($result)) {
      return $this->render($elements + ['elencoprenotazioni' => $this->mapPrenotazioni($result)]);
    }

    return parent::render($result);
  }

  /**
   * Controller Le tue prenotazioni.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotazione(string $id, Request $request) {
    $result = $this->apiService->getPrenotazione($id);
    if (is_a($result, 'Exception')) {
      return parent::render($result->getMessage());
    }
    if (is_object($result) && property_exists($result, 'esito') && property_exists($result, 'descrizione')) {
      return parent::render($result->descrizione);
    }
    if (is_object($result) && property_exists($result, 'roomId')) {
      $elements = [
        'prenotazione' => $result,
        'data'         => $this->mapPrenotazione($result),
        'roomId'       => $result->roomId,
        'caseId'       => $result->numeroPratica,
      ];

      return $this->renderSingle($elements);
    }

    return parent::render("Errore nel caricamento della prenotazione $id");
  }

  /**
   * Controller Prenotazione annulla.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotazioneAnnulla(string $id, Request $request) {
    $annullamento = $this->apiService->annullaPrenotazione($id);
    $result       = $this->apiService->getPrenotazione($id);
    if (is_a($result, 'Exception') || is_a($annullamento, 'Exception')) {
      return parent::render($result->getMessage());
    }
    if (is_object($result) && property_exists($result, 'esito') && property_exists($result, 'descrizione')) {
      return parent::render($result->descrizione);
    }
    if (is_object($result) && property_exists($result, 'roomId')) {
      $elements = [
        'prenotazione' => $result,
        'data'         => $this->mapPrenotazione($result),
      ];

      $render = [
        'content' => [
          '#type'   => 'pattern',
          '#id'     => 'user_prenotazione_dettaglio',
          '#fields' => [
            'page_title'        => $elements['data']['prenotazione_titolo'],
            'breadcrumbs'       => $this->getBreadcrumb(),
            'url_areapersonale' => Url::fromRoute('m_api.prenotame.prenotazioni')
              ->toString(),
            'message'           => $this->t('Booking cancelled'),
          ],
          '#weight' => 10,
        ],
      ];

      return $render;
    }

    return parent::render("Errore nel caricamento della prenotazione $id");
  }

  /**
   * Controller Document.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function document(string $id, Request $request) {
    // Old factory
    // $meta        = $this->apiService->getDocumentMetaData($id);
    // $metaBody    = \GuzzleHttp\json_decode($meta->getBody()->getContents());
    $metaBody = $this->apiService->getDocumentMetaData($id);
    $fileName = $metaBody->fileName;
    // $content     = $this->apiService->getDocumentContent($id);
    // $contentBody = $content->getBody();
    $contentBody = $this->apiService->getDocumentContent($id);

    $response = new StreamedResponse(function () use ($contentBody) {
      // While (!$contentBody->eof()) {
      //   echo $contentBody->read(1024);
      // }.
      echo $contentBody;
    });

    $mimeType = \GuzzleHttp\Psr7\mimetype_from_filename($fileName);

    $response->headers->set('Content-Type', $mimeType);

    return $response;
  }

  /**
   * Controller dettagli sala.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function dettagliSala(string $id, Request $request) {
    $result = $this->apiService->getDettagliSala($id);

    return new JsonResponse($result);
  }

  /**
   * Controller recupera disponibilita sala.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function recuperaDisponibilita(string $id, Request $request) {
    $body = $request->getContent();

    if ($data = json_decode($body)) {
      $result = $this->apiService->recuperaDisponibilita($id, $data);
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

    return new JsonResponse(NULL, 500);
  }

  /**
   * Controller verifica prezzo sala.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function verificaPrezzo(string $id, Request $request) {
    $body = $request->getContent();

    if ($data = json_decode($body)) {
      $result = $this->apiService->verificaPrezzo($id, $data);
      if (is_a($result, 'Exception') && $responseBody = json_decode($result->getResponse()
        ->getBody()
        ->getContents())) {
        return new JsonResponse(
          $responseBody,
          $result->getResponse()->getStatusCode()
        );
      }
      else {
        return new JsonResponse($result);
      }
    }

    return new JsonResponse(NULL, 500);
  }

  /**
   * Controller verifica prezzo sala.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function prenotaSala(string $id, Request $request) {
    $body = $request->getContent();

    if ($data = json_decode($body)) {
      $result = $this->apiService->prenotaSala($id, $body);
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
   * Map api data to template.
   *
   * @param array $prenotazioni
   *   A list of payments.
   *
   * @return array
   *   A parameter array used by twig template.
   */
  private function mapPrenotazioni(array $prenotazioni) {
    $mapStati = $this->getOptionsStati();
    $rooms    = $this->getRooms($prenotazioni);
    $result   = [];
    foreach ($prenotazioni as $prenotazione) {
      $result[] = [
        'prenotazione_titolo'       => array_key_exists($prenotazione->roomId, $rooms) ? $rooms[$prenotazione->roomId] : $prenotazione->roomId,
        'prenotazione_sottotitolo'  => $prenotazione->titoloEvento,
        'prenotazione_stato'        => array_key_exists($prenotazione->stato, $mapStati) ? $mapStati[$prenotazione->stato] : NULL,
        'prenotazione_codice_stato' => $this->remapStato($prenotazione->stato),
        'prenotazione_data'         => $this->getDataPrenotazione($prenotazione),
        'prenotazione_pratica'      => $prenotazione->numeroPratica,
        'prenotazione_orario'       => $this->getOrario($prenotazione),
        'prenotazione_note'         => property_exists($prenotazione, 'note') ? $prenotazione->note : NULL,
        'prenotazione_url'          => $this->linkPrenotazione($prenotazione->numeroPratica),
      ];
    }

    return $result;
  }

  /**
   * Remaps state's number.
   */
  public function remapStato($stato) {
    if ($stato === 1) {
      return 3;
    }
    elseif (in_array($stato, [2, 3, 4])) {
      return 2;
    }
    elseif ($stato === 5) {
      return 4;
    }
    elseif (in_array($stato, [6, 7, 8])) {
      return 5;
    }
    else {
      return $stato;
    }
  }

  /**
   * Map api data to template.
   *
   * @param object $prenotazione
   *   A prenotazione object.
   *
   * @return array
   *   A parameter array used by twig template.
   */
  private function mapPrenotazione(object $prenotazione) {
    $mapStati = $this->getOptionsStati();
    $rooms    = $this->getRooms([$prenotazione]);
    if (empty($rooms)) {
      $elements['title'] = $this->routeMatch->getRouteObject()
        ->getDefault('_title');
    }
    else {
      $elements['title'] = reset($rooms);
    }

    $result = [
      'prenotazione_titolo'       => $elements['title'],
      'prenotazione_sottotitolo'  => $prenotazione->titoloEvento,
      'prenotazione_stato'        => array_key_exists($prenotazione->stato, $mapStati) ? $mapStati[$prenotazione->stato] : NULL,
      'prenotazione_codice_stato' => $prenotazione->stato,
      'prenotazione_data'         => $this->getDataPrenotazione($prenotazione),
      'prenotazione_pratica'      => $prenotazione->numeroPratica,
      'prenotazione_orario'       => $this->getOrario($prenotazione),
      'prenotazione_note'         => property_exists($prenotazione, 'note') ? $prenotazione->note : NULL,
      'prenotazione_importo'      => property_exists($prenotazione, 'importo') ? $prenotazione->importo : NULL,
      'documenti_list'            => property_exists($prenotazione, 'allegati') ? $this->mapAllegati($prenotazione->allegati) : NULL,
      'codice_IUV'                => property_exists($prenotazione, 'iuv') ? $prenotazione->iuv : NULL,
      'url_pagopa'                => property_exists($prenotazione, 'iuv') ? $this->linkPagamento($prenotazione) : NULL,
      'prenotazione_annulla'      => Url::fromRoute('m_api.prenotame.prenotazione_cancel', ['id' => $prenotazione->numeroPratica], ['absolute' => TRUE])
        ->toString(),
    ];

    return $result;
  }

  /**
   * Create a link for payment.
   *
   * @param object $prenotazione
   *   Payment to check.
   * @param string|null $url
   *   A destination to be provided to the payment system.
   * @param bool $check
   *   Let the function check if there is a recepit.
   *
   * @return string|null
   *   A link for payment if due.
   */
  private function linkPagamento(object $prenotazione, ?string $url = NULL, $check = TRUE) {
    if (is_null($url)) {
      $url = Url::fromRoute($this->routeMatch->getRouteName(), ['id' => $prenotazione->numeroPratica], ['absolute' => TRUE])
        ->toString();
    }
    $url_ko = Url::fromUserInput('/servizi/messina/esito/prenotazione/ko', ['absolute' => TRUE]);
    $url_ok = Url::fromUserInput('/servizi/messina/esito/prenotazione/ok', ['absolute' => TRUE]);

    $params = [
      "iuv"        => $prenotazione->iuv,
      "ente"       => $prenotazione->ente,
      "url_ok"     => $url_ok->toString(),
      "url_ko"     => $url_ko->toString(),
      "url_cancel" => $url,
      "url_s2s"    => $url,
    ];
    if (isset($prenotazione->richiedente->codiFisc)) {
      $params['cf'] = $prenotazione->richiedente->codiFisc;
    }

    $result = $this->apiService->pagamentoPosizioneAttesa($params);

    if (property_exists($result, 'url')) {
      return $result->url;
    }

    $this->getLogger(__CLASS__)
      ->error('Link per il pagamento non trovato per IUV: {iuv}', ['iuv' => $prenotazione->iuv]);

    return NULL;
  }

  /**
   * Map api data to template.
   *
   * @param array $allegati
   *   A list of documents.
   *
   * @return array
   *   A parameter array used by twig template.
   */
  private function mapAllegati(array $allegati) {
    $result = [];
    if (empty($allegati)) {
      return $result;
    }
    foreach ($allegati as $key => $allegato) {
      $result[] = [
        'title' => $allegato->fileName,
        'url'   => $this->getAttachmentUrl($allegato->idDocumentale),
      ];
    }

    return $result;
  }

  /**
   * Build a link for the document.
   *
   * @param string $id
   *   Document id.
   *
   * @return string
   *   A link to the document download page.
   */
  private function getAttachmentUrl(string $id) {
    return Url::fromRoute('m_api.prenotame.document', ['id' => $id])
      ->toString();
  }

  /**
   * Filter prenotazioni list on field.
   *
   * @param array &$prenotazioni
   *   List to be filtered.
   * @param string $field
   *   The field on wich apply the filter.
   * @param string $value
   *   The filter value.
   */
  private function filterData(array &$prenotazioni, string $field, string $value) {;
    $format = 'Y-m-d';
    if ($value == '') {
      return;
    }
    switch ($field) {
      case 'data':
        $prenotazioni = array_filter($prenotazioni, function ($prenotazione) use ($value, $format) {
          try {
            $da = DrupalDateTime::createFromFormat($format, $prenotazione->giornoDa);
            $a  = DrupalDateTime::createFromFormat($format, $prenotazione->giornoA);
            if ($da->format($format) === $a->format($format) && $da->format($format) === $value) {
              return TRUE;
            }
            $data_value = DrupalDateTime::createFromFormat($format, $value);
            if ($data_value >= $da && $data_value <= $a) {
              return TRUE;
            }
          }
          catch (\InvalidArgumentException $e) {
            return FALSE;
          }

          return FALSE;
        });

        break;

      case 'stato':
        $prenotazioni = array_filter($prenotazioni, function ($prenotazione) use ($value, $format) {
          return ((string) $prenotazione->stato === $value);
        });

        break;

      default:
        break;
    }
  }

  /**
   * Format time for display.
   *
   * @param object $prenotazione
   *   Booking object.
   *
   * @return string
   *   A formatted value.
   */
  private function getOrario(object $prenotazione) {
    $da = substr($prenotazione->oraDa, 0, -3);
    $a  = substr($prenotazione->oraA, 0, -3);

    return "$da / $a";
  }

  /**
   * Format date for display.
   *
   * @param object $prenotazione
   *   Booking object.
   *
   * @return string
   *   A formatted value.
   */
  private function getDataPrenotazione(object $prenotazione) {
    if (isset($prenotazione->giornoDa) && isset($prenotazione->giornoA)) {
      $da = DrupalDateTime::createFromFormat('Y-m-d', $prenotazione->giornoDa);
      $a  = DrupalDateTime::createFromFormat('Y-m-d', $prenotazione->giornoA);
      if ($da !== FALSE && $a != FALSE) {
        $settings = ['langcode' => 'it'];
        $format   = 'j F';

        return $da->format($format, $settings) . ' / ' . $a->format($format, $settings);
      }
    }

    return NULL;
  }

  /**
   * Load room nodes names based on roomIds.
   *
   * @param array $prenotazioni
   *   A list of booking objects.
   *
   * @return string
   *   An array of roomId => Room name.
   */
  private function getRooms(array $prenotazioni) {
    $ids = array_map(function ($prenotazione) {
      return $prenotazione->roomId;
    }, $prenotazioni);
    if (empty($ids)) {
      return [];
    }
    $rooms  = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'field_room_id' => $ids,
      ]);
    $result = [];
    foreach ($rooms as $nid => $room) {
      $roomId          = $room->get('field_room_id')->getString();
      $result[$roomId] = $room->getTitle();
    }

    return $result;
  }

  /**
   * Render content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function render($elements) {
    $render = [
      'content' => [
        '#type'   => 'pattern',
        '#id'     => 'user_prenotazioni',
        '#fields' => [
          'page_title'         => $this->routeMatch->getRouteObject()
            ->getDefault('_title'),
          'breadcrumbs'        => $this->getBreadcrumb(),
          'elencoprenotazioni' => $elements['elencoprenotazioni'],
          'SelectStato'        => $this->getSelectStati($elements['stato']),
          'FiltroData'         => $this->getFiltroData($elements['data']),
        ],
        '#weight' => 10,
      ],
    ];

    return $render;
  }

  /**
   * Render single content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function renderSingle($elements) {
    $service = \Drupal::service('m_api.prenotame_client');

    $render = [
      'content' => [
        '#type'     => 'pattern',
        '#id'       => 'user_prenotazione_dettaglio',
        '#fields'   => [
          'page_title'        => $elements['data']['prenotazione_titolo'],
          'breadcrumbs'       => $this->getBreadcrumb(),
          'url_areapersonale' => Url::fromRoute('m_api.prenotame.prenotazioni')
            ->toString(),
        ] + $elements['data'],
        '#weight'   => 10,
        '#attached' => [
          'drupalSettings' => [
            'm_api' => [
              'roomId' => $elements['roomId'],
              'caseId' => $elements['caseId'],
              'endpoint' => [
                'dettagliSala' => Url::fromRoute('m_api.prenotame_dettagli_sala', ['id' => $elements['roomId']])->toString(),
              ],
              'id' => $elements['roomId'],
              'cf' => $service->getCurrentCf(),
              'userinfo' => $service->getUserInfo(),
            ],
          ],
          'library' => [
            'm_api/prenotame-allegati',
          ],
        ],
      ],
    ];

    return $render;
  }

  /**
   * Build a link for the booking page.
   *
   * @param string $id
   *   Booking id.
   *
   * @return string
   *   A link to the booking page.
   */
  private function linkPrenotazione(string $id) {
    return Url::fromRoute('m_api.prenotame.prenotazione', ['id' => $id])
      ->toString();
  }

  /**
   * Build a select with status list.
   *
   * @return array
   *   A renderable array.
   */
  private function getSelectStati(?string $stato) {
    $stati = $this->getOptionsStati();
    $id    = 'stato';

    return [
      '#type'     => 'select',
      '#title'    => 'Filtra per stato',
      '#id'       => $id,
      '#options'  => $stati,
      '#value'    => $stato,
      '#chosen'   => FALSE,
      '#weight'   => 1,
      '#attached' => [
        'library'        => ['m_api/page-switcher-select'],
        'drupalSettings' => [
          'm_api' => [
            'switcher' => [
              $id => $this->serviceUrl("$id=$stato"),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Build a date field.
   *
   * @return array
   *   A renderable array.
   */
  private function getFiltroData(?string $data) {
    $id = 'data';

    return [
      '#type'     => 'date',
      '#title'    => 'Data',
      '#id'       => $id,
      '#value'    => $data,
      '#weight'   => 1,
      '#attached' => [
        'library'        => ['m_api/page-switcher-select'],
        'drupalSettings' => [
          'm_api' => [
            'switcher' => [
              $id => $this->serviceUrl("$id=$data"),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Build a list of options for status.
   *
   * @return array
   *   A list of id => status.
   */
  private function getOptionsStati() {
    $stati = ['' => 'Tutti'];
    foreach ($this->apiService->getStatiPrenotazione() as $key => $value) {
      $stati[$value->id] = $value->stato;
    }

    return $stati;
  }

  /**
   * Controller aggiungiAllegati.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return a response object.
   */
  public function aggiungiAllegati(string $id, Request $request) {
    $body = $request->getContent();

    if ($data = json_decode($body)) {
      $result = $this->apiService->aggiungiAllegati($id, $body);
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

    return new JsonResponse(NULL, 500);
  }

}
