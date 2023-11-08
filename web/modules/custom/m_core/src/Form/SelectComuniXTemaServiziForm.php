<?php

namespace Drupal\m_core\Form;

use Drupal\node\NodeInterface;

/**
 * Class SelectComuniXTemaServiziForm.
 */
class SelectComuniXTemaServiziForm extends SelectComuniForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_comuni_tema_servizi_form';
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
    $query->join('node__field_categoria_agg_servizi', 'ftt', 'ftt.entity_id = fc.entity_id');

    $query->condition('fd.type', 'comune_servizi');
    $query->condition('fc.bundle', 'aggregatore_servizi');
    $query->condition('ftt.field_categoria_agg_servizi_target_id', $node->get('field_categoria_agg_servizi')->target_id);
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
