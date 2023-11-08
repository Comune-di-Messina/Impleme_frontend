<?php

namespace Drupal\m_core;

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class MCoreService.
 */
class MCoreService implements MCoreServiceInterface {

  /**
   * Add path luoghi to variables luoghi_path.
   *
   * @param array $variables
   *   Variables where add luoghi_path entry.
   */
  public function setVariablesButtonLearnMore(array &$variables) {
    $node = $this->retrieveCurrentNodeFromRequest();
    if ($node instanceof NodeInterface) {
      $current_path = Drupal::service('path.current')->getPath();
      $uri = Drupal::service('path.alias_manager')
        ->getAliasByPath($current_path);
      $pathUri = explode('/', $uri);
      if (isset($pathUri[1]) && $pathUri[1] == 'servizi') {
        $nid = $this->getNidComune($node);
        if (!is_null($nid)) {
          $comune = Node::load($nid);
          if ($comune->hasField('field_term_comune') &&
            !$comune->get('field_term_comune')->isEmpty()) {
            $comuneTerm = $comune->get('field_term_comune')->getValue();
            if (isset($comuneTerm[0]["target_id"])) {
              $comuneName = Term::load($comuneTerm[0]["target_id"])
                ->get('name')->value;
              $comuneName = (isset($comuneName)) ? strtolower($comuneName) : 'messina';
              $prefix = Drupal::languageManager()
                ->getCurrentLanguage()
                ->getId();
              $variables['luoghi_path'] = '/' . $prefix . '/luoghi/' . $comuneName;
            }
          }
        }
      }
    }
  }

  /**
   * Retrieve Current Node from Request.
   */
  public function retrieveCurrentNodeFromRequest() {
    // Variables.
    $node = Drupal::request()->attributes->get('node');

    if (is_null($node) || empty($node)) {
      $node = Drupal::request()->attributes->get('node_preview');
    }

    if (is_null($node) || empty($node)) {
      $node = Drupal::request()->attributes->get('entity');
    }

    return $node;
  }

  /**
   * Return nid of field comune.
   *
   * @param object $node
   *   Current page Node.
   *
   * @return null|string
   *   Return nid of comune
   */
  public function getNidComune($node) {
    $nid = NULL;
    if (isset($node)) {
      if ($node->getType() == 'comune_servizi') {
        $nid = $node->id();
      }
      if ($node->getType() == 'scheda_servizio') {
        if ($node->hasField('field_comune_servizi')
          && !$node->get('field_comune_servizi')->isEmpty()) {
          $nid = $node->get('field_comune_servizi')->target_id;
        }
      }
      if ($node->getType() == 'aggregatore_servizi') {
        if ($node->hasField('field_comune') && !$node->get('field_comune')
          ->isEmpty()) {
          $nid = $node->get('field_comune')->target_id;
        }
      }
      if ($node->getType() == 'aggregatore_tema') {
        if ($node->hasField('field_comune') && !$node->get('field_comune')
          ->isEmpty()) {
          $nid = $node->get('field_comune')->target_id;
        }
      }
      if ($node->getType() == 'itinerario') {
        if ($node->hasField('field_comune') && !$node->get('field_comune')
          ->isEmpty()) {
          $nid = $node->get('field_comune')->target_id;
        }
      }
      if ($node->getType() == 'evento') {
        if ($node->hasField('field_comune') && !$node->get('field_comune')
          ->isEmpty()) {
          $nid = $node->get('field_comune')->target_id;
        }
      }
      if ($node->getType() == 'sala') {
        if ($node->hasField('field_comune_servizi') && !$node->get('field_comune_servizi')
          ->isEmpty()) {
          $nid = $node->get('field_comune_servizi')->target_id;
        }
      }
      if ($node->getType() == 'comune') {
        if ($node->hasField('field_term_comune') && !$node->get('field_term_comune')
          ->isEmpty()) {
          $nid = $node->get('field_term_comune')->target_id;
        }
      }
    }
    return $nid;
  }

  /**
   * Retrieve Menu and return Referenced Entity in field_categoria_aggregatore.
   *
   * @return array
   *   Return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMenuReferencedEntity(): array {
    $menu_name = 'services';
    $menuArray = [];
    $menu_tree = Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $tree = $menu_tree->load($menu_name, $parameters);
    foreach ($tree as $item) {
      $uuid = $item->link->getDerivativeId();
      if (!empty($uuid)) {
        $entity = Drupal::service('entity.repository')
          ->loadEntityByUuid('menu_link_content', $uuid);
        if (!empty($entity)) {
          if ($entity->hasField('field_categoria_aggregatore')
            && !$entity->get('field_categoria_aggregatore')
              ->isEmpty()) {
            $menuArray[$item->link->getPluginId()] =
              $entity->get('field_categoria_aggregatore')->target_id;
          }
        }
      }
    }
    return $menuArray;
  }

  /**
   * Retrieve logo from comune.
   *
   * @param mixed $variables
   *
   *   Variables Drupal.
   * @param object $comune
   *   Node Landing Comune.
   */
  public function retrieveLogoByCommune(&$variables, $comune) {
    if ($comune instanceof Node) {
      $logo_id = $comune->get('field_logo')->target_id;
      if (isset($logo_id)) {
        $file = Media::load($logo_id);
        if ($file->hasField('field_media_image_1')
          && !$file->get('field_media_image_1')
            ->isEmpty()) {
          $uri = $file->field_media_image_1->entity->getFileUri();
          $variables['field_logo'] = file_create_url($uri);
        }
      }
    }
  }

}
