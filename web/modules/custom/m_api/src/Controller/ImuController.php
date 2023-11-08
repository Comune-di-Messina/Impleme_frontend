<?php

namespace Drupal\m_api\Controller;

use Drupal\m_api\MClientService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImuController.
 */
class ImuController extends MApiControllerBase {

  /**
   * Controller dettaglio situazione Imu.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function dettaglioSituazione(Request $request) {
    $anno = $request->query->get('anno');
    if (is_null($anno) || $anno === 'latest') {
      $anno = $this->getFirstYear();
    }
    $result = $this->apiService->getImuDettaglioSituazione($anno);
    $select = $this->getSelectYears($anno);
    $result->yearSelect = drupal_render($select);
    return $this->render($result);
  }

  /**
   * Call to situazioni service to retrieve available years.
   *
   * @return array
   *   A list of year => year.
   */
  private function getYears() {
    $cache = &drupal_static(__FUNCTION__, []);
    if (empty($cache)) {
      $result = $this->apiService->getImuSituazioni();
      if (is_a($result, 'Exception')) {
        throw $result;
      }
      if (is_object($result) && property_exists($result, 'Esito') && $result->Esito != 'false') {
        $list_years = [];
        foreach ($result->ElencoSituazioni as $key => $situazione) {
          $list_years[$situazione->ElencoAnno] = $situazione->ElencoAnno;
        }
        $cache = $list_years;
        return $cache;
      }
    }
    return $cache;
  }

  /**
   * Build a select with years.
   *
   * @return array
   *   A renderable array.
   */
  private function getSelectYears(string $anno) {
    $list_years = $this->getYears();
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
   * Search the first available year.
   *
   * @return string
   *   The first year with data.
   */
  private function getFirstYear() {
    $list_years = $this->getYears();
    $first_year = reset($list_years);
    return $first_year;
  }

  /**
   * Render content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function render($elements) {
    if (
      $elements->Esito === TRUE ||
      $elements->Esito == MClientService::API_RESULT_OK ||
      $elements->Esito == 'true'
    ) {
      $render = [
        'content' => [
          '#type' => 'pattern',
          '#id' => 'imu',
          '#fields' => [
            'Situazione' => $elements->Situazione,
            'SelectAnni' => $elements->yearSelect,
            'breadcrumbs' => $this->getBreadcrumb(),
          ],
          '#weight' => 10,
        ],
      ];
      return $render;
    }
    else {
      return $this->renderError($elements);
    }
  }

  /**
   * Render content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function renderError($elements) {
    $render = [
      'content' => [
        '#type' => 'pattern',
        '#id' => 'imu',
        '#fields' => [
          'SelectAnni' => $elements->yearSelect,
          'breadcrumbs' => $this->getBreadcrumb(),
          'MsgEsito' => $elements->MsgEsito,
        ],
        '#weight' => 10,
      ],
    ];
    return $render;

  }

}
