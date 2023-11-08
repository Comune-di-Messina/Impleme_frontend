<?php

namespace Drupal\m_core\Form;

use Drupal\node\NodeInterface;

/**
 * Class SelectComuniXTemaForm.
 */
class SelectComuniXTemaForm extends SelectComuniForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_comuni_tema_form';
  }

  /**
   * Get current page node.
   *
   * @return bool|\Drupal\node\NodeInterface|mixed|null
   *   Return node object or FALSE.
   */
  public function getCurrentNode() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      return $node;
    }
    return FALSE;
  }

  /**
   * Get options for "Comuni" select field.
   *
   * @return array
   *   The options array.
   */
  public function getComuni() {
    $node     = $this->getCurrentNode();
    $comuni   = [];
    $database = \Drupal::database();
    $query    = $database->select('node_field_data', 'fd');
    $query->join('node__field_comune', 'fc', 'fc.field_comune_target_id = fd.nid');
    $query->join('node__field_term_tema', 'ftt', 'ftt.entity_id = fc.entity_id');

    $query->condition('fd.type', 'comune');
    $query->condition('fc.bundle', 'aggregatore_tema');
    $query->condition('ftt.field_term_tema_target_id', $node->get('field_term_tema')->target_id);
    $query->condition('fd.status', '1');
    $query->fields('fd', ['title']);
    $query->fields('ftt', ['entity_id']);
    $query->orderBy('title', 'ASC');
    $result = $query->execute();
    foreach ($result as $record) {
      $comuni[$record->entity_id] = $record->title;
    }
    asort($comuni);
    return $comuni;
  }

}
