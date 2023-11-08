<?php

namespace Drupal\m_api\Controller;

use Drupal\m_api\MClientService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TariController.
 */
class TariController extends MApiControllerBase {

  /**
   * Controller dettaglio contribuenti.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function contribuentiDettaglio(Request $request) {
    $codContribuente = $request->query->get('cod-switcher');
    if (is_null($codContribuente) || $codContribuente === 'default') {
      $codContribuente = $this->getFirstContribuente();
    }
    $result = $this->apiService->getTariContribuentiDettaglio($codContribuente);
    $select = $this->getSelectContribuenti($codContribuente);
    $result->contribuentiSelect = drupal_render($select);
    return $this->render($result);
  }

  /**
   * Call to situazioni service to retrieve available contribuenti.
   *
   * @return array
   *   A list of contribuenti => contribuenti.
   */
  private function getContribuenti() {
    $cache = &drupal_static(__FUNCTION__, []);
    if (empty($cache)) {
      $result = $this->apiService->getTariContribuenti();
      if (is_object($result) && property_exists($result, 'Contribuenti')) {
        $list_contribuenti = [];
        foreach ($result->Contribuenti as $key => $contribuente) {
          $list_contribuenti[$contribuente->CodContr] = $contribuente->CodContr;
        }
        $cache = $list_contribuenti;
        return $cache;
      }
    }
    return $cache;
  }

  /**
   * Search the first available contribuente.
   *
   * @return string
   *   The first contribuente with data.
   */
  private function getFirstContribuente() {
    $list = $this->getContribuenti();
    $first = reset($list);
    return $first;
  }

  /**
   * Build a select with contribuenti.
   *
   * @return array
   *   A renderable array.
   */
  private function getSelectContribuenti(string $current) {
    $list = $this->getContribuenti();
    $id = 'cod-switcher';
    return [
      '#type' => 'select',
      '#title' => 'Filtra per contribuente',
      '#id' => $id,
      '#options' => $list,
      '#value' => $current,
      '#chosen' => FALSE,
      '#weight' => 1,
      '#attributes' => [
        'class' => [
          'select-tari--cod-switcher',
        ],
      ],
      '#attached' => [
        'library' => ['m_api/page-switcher-select'],
        'drupalSettings' => [
          'm_api' => [
            'switcher' => [
              $id => $this->serviceUrl($current),
            ],
          ],
        ],
      ],
    ];
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
          '#id' => 'tari',
          '#fields' => [
            'Contribuenti' => $elements->Contribuenti,
            'SelectContribuenti' => $elements->contribuentiSelect,
            'breadcrumbs' => $this->getBreadcrumb(),
            'CfiscPiva' => $elements->CfiscPiva,
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
   * Render error content using PatternLab template.
   *
   * @return string|array
   *   Return a string or a renderable array.
   */
  public function renderError($elements) {
    $render = [
      'content' => [
        '#type' => 'pattern',
        '#id' => 'tari',
        '#fields' => [
          'SelectContribuenti' => $elements->contribuentiSelect,
          'breadcrumbs' => $this->getBreadcrumb(),
          'CfiscPiva' => $elements->CfiscPiva,
          'MsgEsito' => $elements->MsgEsito,
        ],
        '#weight' => 10,
      ],
    ];
    return $render;
  }

}
