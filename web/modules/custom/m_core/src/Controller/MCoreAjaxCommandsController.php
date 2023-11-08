<?php

namespace Drupal\m_core\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class MCoreAjaxCommandsController.
 *
 * Manage the Ajax commands.
 */
class MCoreAjaxCommandsController extends ControllerBase {

  /**
   * Update the carousel content based on section selected.
   *
   * @param string $paragraph_id
   *   The paragraph id.
   * @param string $section
   *   The section selected.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|null
   *   The ajax response to update the markup.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function selettoreSezioneCarosello($paragraph_id, $section) {

    $filter = [
      'aggregatore_servizi',
      'comune_servizi',
      'sala',
      'scheda_servizio',
    ];

    $entity_type = 'paragraph';
    $entity_id = $paragraph_id;
    $view_mode = 'default';

    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);

    $field_carosello = $entity->get('field_elementi_carosello_big')->view($view_mode);

    $updated_array = $this->updateCarouselArray($field_carosello, $section, $filter);

    $rendered = render($updated_array);

    if (isset($rendered)) {
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('.paragraph-' . $paragraph_id . ' .carousel-wrapper', '<div class="carousel-wrapper">' . $rendered->__toString() . '</div>'));

      switch ($section) {
        case 'luoghi':
          $active = '.selector .h-section-luoghi a';
          break;

        default:
          $active = '.selector .h-section-servizi a';
      }

      $arguments = ['active'];
      $base = '.selector a';

      $response->addCommand(new InvokeCommand($base, 'removeClass', $arguments));

      $response->addCommand(new InvokeCommand($active, 'addClass', $arguments));

      return $response;
    }
    return NULL;
  }

  /**
   * Update the carousel content array.
   *
   * @param array $field_carosello
   *   The original array.
   * @param string $section
   *   The section to filter.
   * @param array $filter
   *   The content types to filter.
   *
   * @return array
   *   The updated array.
   */
  public function updateCarouselArray(array $field_carosello, $section, array $filter) {
    $updated_array = $field_carosello;
    foreach ($field_carosello as $key => $item) {
      if (is_array($item) and isset($item["#node"])) {
        $node = $item["#node"];
        switch ($section) {
          case 'servizi':
            if (!in_array($node->getType(), $filter)) {
              unset($field_carosello[$key]);
            }
            break;

          case 'luoghi':
            if (in_array($node->getType(), $filter)) {
              unset($field_carosello[$key]);
            }
            break;
        }
        unset($updated_array[$key]);
      }
    }

    $i = 0;
    foreach ($field_carosello as $key => $item) {
      if (is_array($item) and isset($item["#node"])) {
        $updated_array[$i++] = $item;
      }
    }

    return $updated_array;
  }

}
