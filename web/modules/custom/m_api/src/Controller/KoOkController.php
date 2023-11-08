<?php

namespace Drupal\m_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class KoOkController.
 *
 * @package Drupal\m_api\Controller
 */
class KoOkController extends ControllerBase {
  const OP_TYPE_PAGAMENTO    = 'pagamento';
  const OP_TYPE_SEGNALAME    = 'segnalame';
  const OP_TYPE_PRENOTAZIONE = 'prenotazione';
  const OP_TYPE_ERROR        = 'errore';

  /**
   * Title callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $type
   *   Operation type.
   * @param string $result
   *   Result.
   */
  public function getTitle(Request $request, string $type, string $result) {
    $adjective = $result == 'ok' ? 'completed' : 'error';

    switch ($type) {
      case self::OP_TYPE_PAGAMENTO:
      // phpcs:ignore
      $title = $this->t("Payment $adjective");
        break;

      case self::OP_TYPE_PRENOTAZIONE:
      // phpcs:ignore
      $title = $this->t("Booking $adjective");
        break;

      case self::OP_TYPE_SEGNALAME:
        // phpcs:ignore
        $title = $this->t("Request $adjective");
        break;

      default:
        $title = $this->t('Thank you!');
        break;
    }

    return $title;
  }

  /**
   * Ok/Ko callback for success operations.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $type
   *   The operation's type that will show a different tamplate
   *   based on its value.
   * @param string $result
   *   The result's value.
   */
  public function koOk(Request $request, string $type, string $result) {
    $roomName = $request->get('room_name');

    $opType = $pageTitle = NULL;

    switch ($type) {
      case self::OP_TYPE_PAGAMENTO:
        $opType = self::OP_TYPE_PAGAMENTO;
        if ($result == "ok") {
          $pageTitle = $this->t("Thank you!");
        }
        else {
          $pageTitle = $this->t("Error");
        }
        break;

      case self::OP_TYPE_SEGNALAME:
        $opType = self::OP_TYPE_SEGNALAME;
        if ($result == "ok") {
          $pageTitle = $this->t("Thank you!");
        }
        else {
          $pageTitle = $this->t("Error");
        }
        break;

      case self::OP_TYPE_PRENOTAZIONE:
        $opType = self::OP_TYPE_PRENOTAZIONE;
        if ($result == "ok") {
          $pageTitle = $this->t("Thank you!");
        }
        else {
          $pageTitle = $this->t("Error");
        }
        break;
    }

    return [
      'content' => [
        '#type' => 'pattern',
        '#id' => $result,
        '#fields' => [
          'op_type' => $opType,
          'page_title' => $pageTitle,
          'room_name' => $roomName,
        ],
        '#weight' => 10,
      ],
    ];
  }

}
