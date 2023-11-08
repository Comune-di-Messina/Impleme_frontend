<?php

namespace Drupal\m_core\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use PDO;

/**
 * Class MCoreController.
 */
class MCoreController extends ControllerBase {

  /**
   * Get current page node.
   *
   * @return bool|\Drupal\node\NodeInterface|mixed|null
   *   Return node object or FALSE.
   */
  public function getCurrentNode() {
    $node = Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      return $node;
    }
    return FALSE;
  }

  /**
   * Get Aggregatore page base on Comune from query parameters.
   *
   * @param string $id_comune
   *   The comune node id.
   * @param string $id_parent_tema
   *   The tema term id.
   * @param string $menu
   *   The menu id.
   *
   * @return array
   *   The result array.
   */
  public function getMenuLinkAggregatore($id_comune, $id_parent_tema, $menu) {
    if ($menu == 'main') {
      $database = Drupal::database();
      $query = $database->select('node__field_term_tema', 'ftt');
      $query->join('node__field_comune', 'fc', 'ftt.entity_id = fc.entity_id');

      $query->condition('fc.field_comune_target_id', $id_comune);
      $query->condition('ftt.bundle', 'aggregatore_tema');
      $query->condition('ftt.field_term_tema_target_id', $id_parent_tema);

      $query->fields('ftt', ['entity_id']);

      return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($menu == 'services') {
      return Drupal::entityQuery('node')
        ->condition('type', 'aggregatore_servizi')
        ->condition('field_comune', $id_comune, '=')
        ->condition('field_categoria_agg_servizi', $id_parent_tema, '=')
        ->execute();
    }

  }

  /**
   * Get the Aggregatore node from comune id + child tema id.
   *
   * @param string $id_comune
   *   The comune id.
   * @param string $id_child_tema
   *   The child tema id.
   *
   * @return mixed|null
   *   The entity id of the Aggregatore node.
   */
  public function getAggregatore($id_comune, $id_child_tema) {
    $parents = $this->getParentTerm($id_child_tema);
    $parent  = reset($parents);

    // @reminder specific visitMe case.
    if ($parent->id() === '10') {
      $id_comune = '1';
    };

    $database = Drupal::database();

    $query = $database->select('node__field_term_tema', 'ftt');
    $query->leftJoin('node__field_comune', 'fc', 'ftt.entity_id = fc.entity_id');
    if (isset($id_comune)) {

      /** @var \Drupal\node\Entity\Node $comune */
      $comune = Node::load($id_comune);
      $query->condition('fc.field_comune_target_id', $id_comune);
    }
    else {
      $query->isNull('fc.field_comune_target_id');
    }

    $query->condition('ftt.bundle', 'aggregatore_tema');
    $query->condition('ftt.field_term_tema_target_id', $parent->id());

    $query->fields('ftt', ['entity_id']);

    $result = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

    if (isset($result[0]["entity_id"])) {
      return $result[0]["entity_id"];
    }

    $messenger = Drupal::messenger();
    if (isset($comune) and $comune instanceof Node) {
      $messenger->addMessage(t('È necessario creare un aggregatore collegato al tema "@tema" ed al comune "@comune" per il corretto funzionamento degli argomenti correlati selezionati.', [
        '@tema'   => $parent->getName(),
        '@comune' => $comune->getTitle(),
      ]), $messenger::TYPE_WARNING);
    }
    else {
      $messenger->addMessage(t('È necessario creare un aggregatore collegato al tema "@tema" per il corretto funzionamento degli argomenti correlati selezionati.', ['@tema' => $parent->getName()]), $messenger::TYPE_WARNING);
    }

    return NULL;
  }

  /**
   * Get the Term parents.
   *
   * @param string $term_id
   *   The term to search for parent.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   The parent Term.
   */
  public function getParentTerm($term_id) {
    $parents = Drupal::service('entity_type.manager')
      ->getStorage("taxonomy_term")
      ->loadAllParents($term_id);

    if (isset($parents)) {
      unset($parents[$term_id]);

      return $parents;
    }

    return NULL;
  }

  /**
   * Return the global ct aggregatore from the comune related one.
   *
   * @return array
   *   The options array.
   */
  public function getAggregatoreGlobale($node) {
    $comuni   = [];
    $database = Drupal::database();
    $query    = $database->select('node__field_categoria_agg_servizi', 'fcas');
    $query->join('node_field_data', 'fd', 'fcas.entity_id = fd.nid');
    $query->leftJoin('node__field_comune', 'fc', 'fc.entity_id = fd.nid');

    $query->condition('fd.type', 'aggregatore_servizi');
    $query->condition('fcas.field_categoria_agg_servizi_target_id', $node->get('field_categoria_agg_servizi')->target_id);
    $query->condition('fd.status', '1');
    $query->fields('fd', ['title']);
    $query->fields('fc', ['field_comune_target_id']);
    $query->fields('fcas', ['entity_id']);
    $query->orderBy('title', 'ASC');
    $result = $query->execute();
    foreach ($result as $record) {
      if ($record->field_comune_target_id === NULL) {
        $comuni[$record->entity_id] = $record->title;
      }
    }
    asort($comuni);
    return $comuni;
  }

}
