<?php

namespace Drupal\m_api\Controller;

use Drupal\m_api\Form\AssistanceForm;
use Drupal\m_api\MClientService;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;

/**
 * Class VisureController.
 */
class VisureController extends MApiControllerBase {

  const DOC_TYPE_MATRIMONIO = 'matrimonio';
  const DOC_TYPE_ANAGRAFICA = 'anagrafica';
  const DOC_TYPE_NASCITA = 'nascita';
  const DOC_TYPE_RESIDENZA = 'residenza';
  const DOC_TYPE_STATOFAMIGLIA = 'StatoFamiglia';

  /**
   * Controller Visure.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function visure() {
    $aire = $this->apiService->getVisureAire();
    if (property_exists($aire, 'ricercaSoggettiResponse') && $aire->ricercaSoggettiResponse->esito->codice === MClientService::API_RESULT_OK) {
      // $famiglia = $this->apiService->getVisureAprFamiglia();
    }

    return $this->render($aire);
  }

  /**
   * Controller Visure nucleo familiare.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function visureFamiglia() {
    $famiglia = $this->apiService->getVisureAprFamiglia();
    if (property_exists($famiglia, 'ricercaSoggettiResponse')) {
      $persone = $famiglia->ricercaSoggettiResponse->individuoCompleto->componenti;
      $famiglia->persone = $persone;
    }

    return $this->renderFamiglia($famiglia);
  }

  /**
   * Controller richiesta Aire.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function richiestaAire() {
    $result = $this->apiService->getVisureAire();
    return $this->render($result);
  }

  /**
   * Controller richiesta Apr.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function richiestaApr() {
    $result = $this->apiService->getVisureApr();
    return $this->render($result);
  }

  /**
   * Controller richiesta Apr con famiglia.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function richiestaAprFamiglia() {
    $result = $this->apiService->getVisureAprFamiglia();
    return $this->render($result);
  }

  /**
   * Controller PDF Matrimonio.
   *
   * @param string $docType
   *   Document type.
   * @param string $cfenc
   *   Encoded fiscal code.
   * @param string $action
   *   Browser action, can be 'view' or 'download'.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function richiestaPdf(string $docType, string $cfenc, string $action) {
    $types = [
      self::DOC_TYPE_MATRIMONIO,
      self::DOC_TYPE_ANAGRAFICA,
      self::DOC_TYPE_NASCITA,
      self::DOC_TYPE_RESIDENZA,
      self::DOC_TYPE_STATOFAMIGLIA,
    ];
    if (!in_array($docType, $types)) {
      return $this->render($this->t('Document not found')->render());
    }
    $cf = $this->decode($cfenc);
    $result = $this->apiService->getVisurePdf($cf, $docType);
    return $this->handlePdfResponse($result, $action, "$docType.pdf");
  }

  /**
   * Render content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function render($elements) {
    if (
        property_exists($elements, 'ricercaSoggettiResponse')
        && property_exists($elements->ricercaSoggettiResponse, 'esito')
        && $elements->ricercaSoggettiResponse->esito->codice == MClientService::API_RESULT_OK
      ) {
      $render = [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'visure',
          '#fields' => [
            'ricercaSoggettiResponse' => $elements->ricercaSoggettiResponse,
            'breadcrumbs' => $this->getBreadcrumb(),
            'visure_dati' => Url::fromRoute('m_api.visure'),
            'visure_nucleofam' => Url::fromRoute('m_api.visure_famiglia'),
            'formQualcosaNonTorna' => \Drupal::formBuilder()->getForm(AssistanceForm::class),
          ],
          '#weight' => 10,
        ],
      ];
      return $render;
    }
    elseif (
      property_exists($elements, 'esito')
      && property_exists($elements->esito, 'testo')
    ) {
      $render = [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'visure',
          '#fields' => [
            'errore' => $elements->esito->testo,
            'breadcrumbs' => $this->getBreadcrumb(),
            'visure_dati' => Url::fromRoute('m_api.visure'),
            'visure_nucleofam' => Url::fromRoute('m_api.visure_famiglia'),
          ],
          '#weight' => 10,
        ],
      ];
      return $render;
    }
    else {
      return parent::render($elements);
    }
  }

  /**
   * Render content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function renderFamiglia($elements) {
    if (
        property_exists($elements, 'ricercaSoggettiResponse')
        && property_exists($elements->ricercaSoggettiResponse, 'esito')
        && $elements->ricercaSoggettiResponse->esito->codice == MClientService::API_RESULT_OK
      ) {
      $render = [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'visure_nucleo',
          '#fields' => [
            'elencopersone' => $elements->persone,
            'breadcrumbs' => $this->getBreadcrumb(),
            'visure_dati' => Url::fromRoute('m_api.visure'),
            'visure_nucleofam' => Url::fromRoute('m_api.visure_famiglia'),
          ],
          '#weight' => 10,
        ],
        '#attached' => [
          'library' => ['m_api/pdf-select'],
          'drupalSettings' => [
            'm_api' => [
              'enabledDocs' => [],
              'docEndpoint' => Url::fromRoute('m_api.visure_pdf', ['docType' => 'TYPE', 'cfenc' => 'CF_ENC'])->toString(),
            ],
          ],
        ],
      ];
      return $render;
    }
    elseif (
      property_exists($elements, 'esito')
      && property_exists($elements->esito, 'testo')
    ) {
      $render = [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'visure_nucleo',
          '#fields' => [
            'errore' => $elements->esito->testo,
            'breadcrumbs' => $this->getBreadcrumb(),
            'visure_dati' => Url::fromRoute('m_api.visure'),
            'visure_nucleofam' => Url::fromRoute('m_api.visure_famiglia'),
          ],
          '#weight' => 10,
        ],
      ];
      return $render;
    }
    else {
      return parent::render($elements);
    }
  }

  /**
   * Handle the response for PDF results.
   *
   * @param object $result
   *   Result of API Call.
   * @param string $action
   *   Browser action, can be 'view' or 'download'.
   * @param string $filename
   *   A filename for PDF.
   *
   * @return \Symfony\Component\HttpFoundation\Response|array
   *   A Response object or a renderable array.
   */
  private function handlePdfResponse(object $result, string $action, string $filename = 'CertificatoDiMatrimonio.pdf') {
    if (is_object($result) && property_exists($result, 'esito')) {
      if ($result->esito->codice === MClientService::API_RESULT_KO) {
        return $this->render($result->esito->testo);
      }
      $response = new Response();
      if ($filename) {
        $disposition = ($action === 'download') ? 'attachment' : 'inline';
        $response->headers->set('Content-Disposition', "$disposition; filename=\"$filename\"");
      }
      $response->headers->set('Content-Type', 'application/pdf');
      $response->headers->set('Cache-Control', 'private,max-age=300');
      $response->setContent(base64_decode($result->pdf));
      return $response;
    }
    return $this->render($result);
  }

  /**
   * Decode a string using rot13.
   *
   * @param string $input
   *   The input string.
   *
   * @return string
   *   Decoded string.
   */
  private function decode(string $input) {
    // Return str_rot13($input);.
    return $input;
  }

}
