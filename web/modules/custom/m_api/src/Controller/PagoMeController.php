<?php

namespace Drupal\m_api\Controller;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PagoMeController.
 */
class PagoMeController extends MApiControllerBase {

  /**
   * Elenco ricevute per codice fiscale.
   *
   * @var array
   */
  private $ricevute;

  /**
   * Controller I tuoi pagamenti.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function pagamenti(Request $request) {
    $anno = $request->query->get('anno');
    if (is_null($anno)) {
      $anno = (new \DateTime('today'))->format('Y');
    }
    if (intval($anno) != $anno) {
      return parent::render('Parametro non valido: anno.');
    }
    $start = '01/01/' . $anno;
    $end = '31/12/' . $anno;

    $elements = [
      'anno' => $anno,
    ];
    $idTributo = $request->query->get('idTributo');
    if (is_null($idTributo) || $idTributo === 'all') {
      $idTributo = '';
      $elements['idTributo'] = 'all';
    }
    else {
      $elements['idTributo'] = $idTributo;
    }

    $result = $this->apiService->getPosizioniPagate($start, $end, $idTributo);

    if (is_a($result, 'Exception')) {
      return parent::render($result->getMessage());
    }
    if (is_object($result) && property_exists($result, 'esito') && property_exists($result, 'descrizione')) {
      // Return parent::render($result->descrizione);.
      return $this->render($elements + ['errore' => 'Nessun pagamento corrisponde ai filtri indicati. Prova a cambiare anno o servizio selezionati.'/*$result->descrizione*/]);
    }
    if (empty($this->getRicevute($start, $end))) {
      $this->getLogger(__CLASS__)->warning('Nessuna ricevuta trovata.');
    }

    if (is_object($result) && property_exists($result, 'elencoPartiteDebitorie')) {
      return $this->render($elements + ['elencopagamenti' => $this->mapPagamenti($result->elencoPartiteDebitorie)]);
    }
    if (is_array($result)) {
      return $this->render($elements + ['elencopagamenti' => $this->mapPagamenti($result)]);
    }
    return parent::render($result);
  }

  /**
   * Controller per ricevute.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function ricevuta(string $id) {
    $file = $this->apiService->getRtById($id);
    if (is_null($file)) {
      return parent::render('File non trovato');
    }
    return $this->handlePdfResponse($file, 'download');
  }

  /**
   * Controller Le tue posizioni debitorie.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function posizioniDebitorie(Request $request) {
    $anno = $request->query->get('anno');
    if (is_null($anno)) {
      $anno = (new \DateTime('today'))->format('Y');
    }

    if (intval($anno) != $anno) {
      return parent::render('Parametro non valido: anno.');
    }
    $start = '01/01/' . ($anno);
    $end = '31/12/' . $anno;

    $elements = [
      'anno' => $anno,
    ];
    $idTributo = $request->query->get('idTributo');
    if (is_null($idTributo) || $idTributo === 'all') {
      $idTributo = '';
      $elements['idTributo'] = 'all';
    }
    else {
      $elements['idTributo'] = $idTributo;
    }
    $result = $this->apiService->getPosizioniNonPagate($start, $end, $idTributo);

    if (is_a($result, 'Exception')) {
      return parent::render($result->getMessage());
    }
    if (is_object($result) && property_exists($result, 'esito') && property_exists($result, 'descrizione')) {
      return $this->render($elements + ['errore' => 'Nessuna posizione debitoria corrisponde ai filtri indicati. Prova a cambiare anno o servizio selezionati.'/*$result->descrizione*/]);
    }
    if (is_array($result)) {
      return $this->render($elements + ['elencopagamenti' => $this->mapPagamenti($result)]);
    }
    return parent::render($result);
  }

  /**
   * Controller Pagamento con avviso.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function avviso(?string $iuv, Request $request) {
    $cf = ($this->currentUser()->isAuthenticated()) ? $this->apiService->getCurrentCf() : NULL;

    return [
      'content' => [
        '#type' => 'pattern',
        '#id' => 'pagofacile_avviso',
        '#fields' => [
          'page_title' => $this->routeMatch->getRouteObject()->getDefault('_title'),
          'page_content' => 'Inserisci i dati per effettuare il pagamento',
          'back' => '<a href="javascript:history.back()">' . t('Torna indietro') . '</a>',
          'breadcrumbs' => $this->getBreadcrumb(),
          'error' => ($iuv === 'error') ? TRUE : FALSE,
          'inputIUV' => $iuv,
          'inputCf' => $cf,
        ],
        '#weight' => 10,
        '#attached' => [
          'library' => ['m_api/pagome-avviso'],
          'drupalSettings' => [
            'm_api' => [
              'pagome' => [
                'url' => Url::fromRoute('m_api.pagome_payment')->toString(),
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Controller check IUV.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return Json Response.
   */
  public function iuv(Request $request) {
    $iuv = $request->query->get('iuv');
    $cf = $request->query->get('cod_fiscale');
    $destination = $request->query->get('destination');
    $pagamento = [
      'iuv' => $iuv,
      'ente' => $this->ente,
      'cf' => $cf,
    ];
    if ($destination) {
      $destination = Url::fromUri('internal:/' . $destination, ['absolute' => TRUE])->toString();
    }
    $url = $this->linkPagamento((object) $pagamento, $destination);

    if (is_a($url, 'Exception')) {
      return new JsonResponse(
        ['esito' => 'KO', 'descrizione' => $ricevute->getMessage()]
      );
    }
    if (is_object($url) && isset($url->esito) && $ricevute->esito === 'KO') {
      return new JsonResponse($url);
    }

    if (is_string($url)) {
      $response = new JsonResponse($url);
      return $response;
    }
    return new JsonResponse(
      ['esito' => 'KO', 'descrizione' => 'IUV non presente.']
    );
  }

  /**
   * Controller Le tue ricevute telematiche.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function ricevute(string $iuv = NULL, string $replace = '') {
    if ($iuv == NULL || $iuv === 'error') {
      return [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'user_ricevute',
          '#fields' => [
            'page_title' => $this->routeMatch->getRouteObject()->getDefault('_title'),
            'breadcrumbs' => $this->getBreadcrumb(),
            'error' => ($iuv === 'error') ? TRUE : FALSE,
          ],
          '#weight' => 10,
          '#attached' => [
            'library' => ['m_api/iuv-search'],
            'drupalSettings' => [
              'm_api' => [
                'iuv_search' => [
                  'url' => $this->serviceUrl($replace),
                ],
              ],
            ],
          ],
        ],
      ];
    }

    $ricevute = $this->apiService->verificaPresenzaRtByIuv($iuv);

    if (is_a($ricevute, 'Exception') || (isset($ricevute->esito) && $ricevute->esito === 'KO')) {
      return $this->ricevute('error', $iuv);
    }
    $ricevuta = reset($ricevute);
    return $this->ricevuta($ricevuta->id_pdf_documentale);
  }

  /**
   * Controller Pagamento spontaneo.
   *
   * @param int $idServizio
   *   Service ID.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function spontaneo($idServizio) {
    if ($idServizio > 0) {
      $servizio = \Drupal::entityTypeManager()->getStorage('node')->load($idServizio);
      $cover = $servizio->get('field_cover');
    }
    if (isset($cover)) {
      $cover = $cover->referencedEntities();
      if (count($cover) > 0) {
        $cover = $cover[0]->field_media_image->getValue()[0]['target_id'];
        $cover = File::load($cover)->url();
      }
    }
    else {
      $cover = FALSE;
    }

    $servizi = $this->apiService->getTributiEnte($this->ente);
    $anni = [];
    if (is_array($servizi)) {
      $anni = $this->anniFromSevizi($servizi);
    }

    $image = isset($cover) ? $cover : '/themes/custom/portalemessina/dist/images/hero/placeholder.jpg';

    return [
      'header' => [
        '#type' => 'pattern',
        '#id' => 'hero_simple',
        '#fields' => [
          'title' => $this->routeMatch->getRouteObject()->getDefault('_title'),
          'breadcrumbs' => $this->getBreadcrumb(),
          'content' => 'Inserisci i dati per effettuare il pagamento',
          'image' => '<img src="' . $image . '">',
          'back' => '<a href="javascript:history.back()">' . t('Torna indietro') . '</a>',
        ],
      ],
      'vue' => [
        '#markup' => '<div id="page"></div>',
        '#attached' => [
          'library' => ['m_api/pagome-spontaneo'],
          'drupalSettings' => [
            'm_api' => [
              'spontaneo' => [
                'servizi' => $servizi,
                'anni' => $anni,
                'ente' => $this->ente,
                'userinfo' => $this->apiService->getUserInfo() +
                  ['cf' => $this->apiService->getCurrentCf()],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Helper function that extract years form service definitions.
   *
   * @param array $servizi
   *   Service definitions.
   *
   * @return array
   *   An array to be used as select options.
   */
  private function anniFromSevizi(array $servizi) {
    $anni = [];
    if (is_array($servizi)) {
      foreach ($servizi as $key => $value) {
        $anni[$value->anno] = $value->anno;
      }
    }
    $result = [];
    foreach ($anni as $key => $value) {
      $result[] = ['value' => $value, 'text' => $value];
    }

    return $result;
  }

  /**
   * Call api to verificaPresenzaRtByCf.
   *
   * @param string $start
   *   Start date.
   * @param string $end
   *   End date.
   *
   * @return array
   *   A list of recepit for current user.
   */
  private function getRicevute(string $start, string $end) {
    if ($this->ricevute === NULL) {
      $this->ricevute = $this->apiService->verificaPresenzaRtByCf($start, $end);
    }

    return $this->ricevute;
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
        '#type' => 'pattern',
        '#id' => 'user_pagamenti',
        '#fields' => [
          'page_title' => $this->routeMatch->getRouteObject()->getDefault('_title'),
          'breadcrumbs' => $this->getBreadcrumb(),
          'elencopagamenti' => array_key_exists('elencopagamenti', $elements) ? $elements['elencopagamenti'] : NULL,
          'SelectAnni' => $this->getSelectYears($elements['anno']),
          'SelectServizi' => $this->getSelectTributi($this->ente, $elements['idTributo']),
          'errore' => array_key_exists('errore', $elements) ? $elements['errore'] : NULL,
        ],
        '#weight' => 10,
      ],
    ];
    return $render;
  }

  /**
   * Map api data to template.
   *
   * @param array $pagamenti
   *   A list of payments.
   *
   * @return array
   *   A parameter array used by twig template.
   */
  private function mapPagamenti(array $pagamenti) {
    $result = [];
    foreach ($pagamenti as $pagamento) {
      $result[] = [
        'title' => $pagamento->descr_tributo,
        'pagamento_anno' => $pagamento->anno_tributo,
        'pagamento_ente' => $pagamento->ente,
        'pagamento_servizio' => $this->getServizio($pagamento),
        'pagamento_causale' => $pagamento->causale_debito,
        'pagamento_emissione' => $pagamento->data_emissione,
        'pagamento_scadenza' => $pagamento->data_scadenza,
        'pagamento_pagamento' => $pagamento->data_inserimento,
        'pagamento_iuv' => $pagamento->iuv,
        'pagamento_importo' => $pagamento->importo_debito,
        'pagamento_stato' => $this->getStato($pagamento),
        'pagamento_url_ricevuta' => $this->linkRicevuta($pagamento),
        'pagamento_url_paga' => $this->linkPagamento($pagamento),
        'codice_fiscale' => $pagamento->cf_piva_debitore,
        'codice_servizio' => $pagamento->id_tributo,
      ];
    }
    return $result;
  }

  /**
   * Build recepit link if check succeed.
   *
   * @param object $pagamento
   *   Payment to check.
   *
   * @return string
   *   Url for recepit download.
   */
  private function linkRicevuta(object $pagamento) {
    if (!property_exists($pagamento, 'ricevuta_telematica')) {
      return NULL;
    }
    $idPdfDocuemntale = $pagamento->ricevuta_telematica->id_pdf_documentale;
    if (!empty($idPdfDocuemntale)) {
      return Url::fromRoute('m_api.pagome_ricevuta', ['id' => $idPdfDocuemntale])->toString();
      /*
       * foreach ($this->ricevute as $key => $ricevuta) {
       * if ($ricevuta->id_pdf_documentale === $idPdfDocuemntale
       * && $ricevuta->iuv === $pagamento->iuv) {
       * return Url::fromRoute(
       * 'm_api.pagome_ricevuta', ['id' => $idPdfDocuemntale])->toString();
       * }
       * }
       */
    }
    return NULL;
  }

  /**
   * Extract payment status form payment if present.
   *
   * @param object $pagamento
   *   A payment object.
   *
   * @return string|null
   *   The payment status or null if not present.
   */
  private function getStato(object $pagamento) {
    if (!property_exists($pagamento, 'ricevuta_telematica')) {
      return NULL;
    }
    return $pagamento->ricevuta_telematica->stato_pagamento;
  }

  /**
   * Create a link for payment.
   *
   * @param object $pagamento
   *   Payment to check.
   * @param string|null $url
   *   A destination to be provided to the payment system.
   * @param bool $check
   *   Let the function check if there is a recepit.
   *
   * @return string|null
   *   A link for payment if due.
   */
  private function linkPagamento(object $pagamento, ?string $url = NULL, $check = TRUE) {
    if ($check && $this->getStato($pagamento) !== NULL) {
      return NULL;
    }
    if (is_null($url)) {
      $url = Url::fromRoute($this->routeMatch->getRouteName(), [], ['absolute' => TRUE])->toString();
    }
    $url_ko = Url::fromUserInput('/servizi/messina/esito/pagamento/ko', ['absolute' => TRUE]);
    $url_ok = Url::fromUserInput('/servizi/messina/esito/pagamento/ok', ['absolute' => TRUE]);
    $params = [
      "iuv" => $pagamento->iuv,
      "ente" => $pagamento->ente,
      "url_ok" => $url_ok->toString(),
      "url_ko" => $url_ko->toString(),
      "url_cancel" => $url,
      "url_s2s" => $url,
    ];
    if (isset($pagamento->cf)) {
      $params['cf'] = $pagamento->cf;
    }
    if (isset($pagamento->codiceFiscale)) {
      $params['cf'] = $pagamento->codiceFiscale;
    }

    $result = $this->apiService->pagamentoPosizioneAttesa($params);

    if (is_a($result, 'Exception')) {
      throw $result;
    }
    if (property_exists($result, 'url')) {
      return $result->url;
    }

    $this->getLogger(__CLASS__)->error('Link per il pagamento non trovato per IUV: {iuv}', ['iuv' => $pagamento->iuv]);
    return NULL;
  }

  /**
   * Extract service name from service code.
   *
   * @param object $pagamento
   *   A payment object.
   *
   * @return string|null
   *   The payment status or null if not present.
   */
  private function getServizio(object $pagamento) {
    if (!property_exists($pagamento, 'id_tributo')) {
      return '';
    }
    $servizi = $this->apiService->getTributiEnte($this->ente);

    foreach ($servizi as $key => $servizio) {
      if ($servizio->IDTributo == $pagamento->id_tributo) {
        return $servizio->NomeTributo;
      }
    }
    return '';
  }

  /**
   * Handle the response for PDF results.
   *
   * @param string $result
   *   Result of API Call.
   * @param string $action
   *   Browser action, can be 'view' or 'download'.
   * @param string $filename
   *   A filename for PDF.
   *
   * @return \Symfony\Component\HttpFoundation\Response|array
   *   A Response object or a renderable array.
   */
  private function handlePdfResponse(string $result, string $action, string $filename = 'Ricevuta.pdf') {
    $response = new Response();
    if ($filename) {
      $disposition = ($action === 'download') ? 'attachment' : 'inline';
      $response->headers->set('Content-Disposition', "$disposition; filename=\"$filename\"");
    }
    $response->headers->set('Content-Type', 'application/pdf');
    // $response->headers->set('Cache-Control', 'private,max-age=300');
    $response->setContent($result);
    return $response;
  }

  /**
   * Build a select with years.
   *
   * @return array
   *   A renderable array.
   */
  private function getSelectYears(string $anno) {
    $list_years = $this->getOptionsYears();
    $id = 'anno';
    return [
      '#type' => 'select',
      '#title' => 'Filtra per anno',
      '#id' => $id,
      '#options' => $list_years,
      '#value' => $anno,
      '#chosen' => FALSE,
      '#weight' => 1,
      '#attached' => [
        'library' => ['m_api/page-switcher-select'],
        'drupalSettings' => [
          'm_api' => [
            'switcher' => [
              $id => $this->serviceUrl("$id=$anno"),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Build an array of options from current year backward.
   *
   * @return array
   *   A list of year => year.
   */
  private function getOptionsYears() {
    $options = [];
    $now = (new \DateTime('today'))->format('Y');
    for ($i = 0; $i < 5; $i++) {
      $options[$now - $i] = $now - $i;
    }
    return $options;
  }

  /**
   * Build a select with tributi.
   *
   * @return array
   *   A renderable array.
   */
  private function getSelectTributi(string $ente, string $current) {
    $list_tributi = $this->getOptionsTributi($ente);
    $id = 'idTributo';
    return [
      '#type' => 'select',
      '#title' => 'Filtra per servizio',
      '#id' => $id,
      '#options' => $list_tributi,
      '#value' => $current,
      '#chosen' => FALSE,
      '#weight' => 1,
      '#attached' => [
        'library' => ['m_api/page-switcher-select'],
        'drupalSettings' => [
          'm_api' => [
            'switcher' => [
              $id => $this->serviceUrl("$id=$current"),
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Build an array of options for tributi.
   *
   * @return array
   *   A list of year => year.
   */
  private function getOptionsTributi(string $ente) {
    $options = [
      'all' => 'Tutti',
    ];
    $list = $this->apiService->getTributiEnte($ente);
    foreach ($list as $tributo) {
      $options[$tributo->IDTributo] = $tributo->NomeTributo;
    }
    return $options;
  }

  // Servizi API per pagamento spontaneo.

  /**
   * Controller API Tariffe.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function apiTariffe(string $idTributo) {
    $result = $this->apiService->getTariffeTributi($this->ente, $idTributo);
    if (is_a($result, 'Exception')) {
      return new JsonResponse('', 404);
    }
    return new JsonResponse($result);
  }

  /**
   * Controller API Richiesta di pagamento spontaneo.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function richiediPagamento(Request $request) {
    $body = $request->getContent();

    if ($payload = json_decode($body)) {
      $result = $this->apiService->requestPayment($payload);
    }
    else {
      return new JsonResponse('', 400);
    }
    if (is_a($result, 'Exception')) {
      return new JsonResponse('', 404);
    }

    if (property_exists($result, 'iuv')) {
      $result->ente = $payload->ente;
      $destination = Url::fromRoute('m_api.pagome_spontaneo', ['idServizio' => 0], ['absolute' => TRUE])->toString();
      $result->url = $this->linkPagamento($result, $destination, FALSE);
    }
    return new JsonResponse($result);
  }

}
