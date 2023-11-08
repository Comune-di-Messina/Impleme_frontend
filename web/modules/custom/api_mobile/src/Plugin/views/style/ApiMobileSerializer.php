<?php

namespace Drupal\api_mobile\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\api_mobile\Constants;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "api_mobile_serializer",
 *   title = @Translation("API Mobile Serializer"),
 *   help = @Translation("Serializes views row data using the Serializer component."),
 *   display_types = {"data"}
 * )
 */
class ApiMobileSerializer extends Serializer {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    $results = [];
    $displayId = $this->view->getDisplay()->display['id'];

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }

    // First of all checks for 'errors'.
    if (isset($this->view->errors)) {
      return $this->serializer->serialize($this->view->errors, $content_type, ['views_style_plugin' => $this]);
    }
    else {
      // Obtain nextIndex from view's result and remove to avoid rendering
      // errors.
      $nextIndex = isset($this->view->result['nextIndex']) ? $this->view->result['nextIndex'] : NULL;
      if ($nextIndex) {
        unset($this->view->result['nextIndex']);
      }

      // Obtain itinerary (if exists) from view's result and remove
      // to avoid rendering errors.
      $itinerary = isset($this->view->result['itinerary']) ? $this->view->result['itinerary'] : NULL;
      if ($itinerary !== NULL) {
        unset($this->view->result['itinerary']);
      }

      // Builds $rows array.
      foreach ($this->view->result as $row_index => $row) {
        $this->view->row_index = $row_index;
        $rows[] = $this->view->rowPlugin->render($row);
      }
      unset($this->view->row_index);

      // Checks for rows named with <prefix>__<fieldName> that it's a trick to
      // create an object (to return) named <prefix> containing all
      // <fieldName>s.
      foreach ($rows as &$row) {
        foreach ($row as $fieldName => $fieldValue) {
          if (strpos($fieldName, '__')) {
            // Split $parts to have on the left side the object's name and
            // on the right side the object's properties.
            $parts = explode('__', $fieldName);
            $row[$parts[0]][$parts[1]] = $fieldValue;
            // Unset raw variable from row.
            unset($row[$fieldName]);
          }
        }
      }

      // If hasNextIndex is true means that is a "list" and not "detail"
      // callback.
      if (isset(Constants::MANDATORY_PARAMETERS[$displayId])) {
        if (Constants::VIEW_OPTIONS[$displayId]['hasNextIndex']) {
          // Get collection name: eg. 'items', 'aggregators', etc...
          $collectionNameKey = isset(Constants::VIEW_OPTIONS[$displayId]['collectionName']) ? Constants::VIEW_OPTIONS[$displayId]['collectionName'] : 'items';
          // So, in this case more results are expected.
          $results += [$collectionNameKey => $rows];
          // Add nextIndex to results.
          $results += ['nextIndex' => $nextIndex];
        }
        else {
          if (isset($itinerary)) {
            $resetRows = reset($rows);
            if (count($itinerary) === 1) {
              $itinerary = $itinerary[0];
            }
            $results = array_merge($resetRows, ['content' => ($itinerary !== FALSE) ? (!empty($itinerary) ? $itinerary : NULL) : NULL]);
          }
          else {
            $results = $rows;
          }
        }
      }

      // Returns JSON serialized $results.
      return $this->serializer->serialize($results, $content_type, ['views_style_plugin' => $this]);
    }
  }

}
