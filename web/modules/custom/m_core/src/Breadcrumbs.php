<?php

namespace Drupal\m_core;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use PDO;

/**
 * Class Breadcrumb.
 *
 * @package Drupal\m_core
 */
class Breadcrumbs implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The user currently logged in.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Construct for Breadcrumbs.
   */
  public function __construct(AccountInterface $current_user, AccessManagerInterface $access_manager) {
    $this->currentUser   = $current_user;
    $this->accessManager = $access_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $current_theme = Drupal::service('theme.manager')->getActiveTheme();
    if ($current_theme->getName() === 'seven') {
      return FALSE;
    }

    if (Drupal::service('path.matcher')->isFrontPage()) {
      return FALSE;
    }

    if ($node = $route_match->getParameter('node')) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $access = $this->accessManager->check($route_match, $this->currentUser, NULL, TRUE);

    $breadcrumb->addCacheableDependency($access);
    $breadcrumb->addCacheContexts(['url.path']);

    $links = $this->getLinks($route_match);

    return $breadcrumb->setLinks(array_reverse($links));
  }

  /**
   * Return links to build breadcrumbs.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route object.
   *
   * @return array
   *   Links to build the breadcrumbs.
   */
  public static function getLinks(RouteMatchInterface $route_match) {
    $links = [];

    /** @var \Drupal\node\Entity\Node $node */
    $node = $route_match->getParameter('node');
    if (isset($node)) {
      $links[] = Link::createFromRoute($node->getTitle(), '<none>');

      switch ($node->getType()) {
        case 'scheda':
        case 'evento':
        case 'itinerario':
          $comune_id = $node->get('field_comune')->target_id;

          $tema_term_id = $node->get('field_term_temi')->target_id;
          if (isset($tema_term_id)) {

            /** @var \Drupal\taxonomy\TermStorage $taxonomy_term_type_manager */
            try {
              $taxonomy_term_type_manager = Drupal::entityTypeManager()
                ->getStorage('taxonomy_term');
            }
            catch (InvalidPluginDefinitionException $e) {
            }
            catch (PluginNotFoundException $e) {
            }

            $parent = $taxonomy_term_type_manager->loadParents($tema_term_id);
            $parent = reset($parent);

            if ($parent instanceof Term) {
              $database = Drupal::database();
              $query    = $database->select('node__field_term_tema', 'ftt');

              if (isset($comune_id)) {

                /** @var \Drupal\node\Entity\Node $comune */
                $comune = Node::load($comune_id);
                $query->join('node__field_comune', 'fc', 'ftt.entity_id = fc.entity_id');
                $query->condition('fc.field_comune_target_id', $comune_id);
              }
              $query->condition('ftt.bundle', 'aggregatore_tema');
              $query->condition('ftt.field_term_tema_target_id', $parent->id());

              $query->fields('ftt', ['entity_id']);

              $result = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
              if (count($result) > 1) {
                foreach ($result as $key => $entity_info) {
                  $database = Drupal::database();
                  $query    = $database->select('node__field_comune', 'fc');
                  $query->condition('fc.entity_id', $entity_info['entity_id']);
                  $query->fields('fc', ['entity_id']);
                  $result = $query->execute()->fetchAll(PDO::FETCH_ASSOC);
                  if (empty($result)) {
                    $result[0]["entity_id"] = $entity_info['entity_id'];
                    break;
                  }
                }
              }
              $messenger = Drupal::messenger();

              if (!empty($result)) {

                /** @var \Drupal\node\Entity\Node $aggregatore */
                $aggregatore = Node::load($result[0]["entity_id"]);
              }
              elseif (isset($comune) and $comune instanceof Node) {
                $messenger->addMessage(t('It is necessary to create an aggregator linked to the theme "@tema" and the city "@comune" for the breadcrumbs to work properly.', ['@tema' => $parent->getName(), '@comune' => $comune->getTitle()]), $messenger::TYPE_WARNING);
              }
              else {
                $messenger->addMessage(t('It is necessary to create an aggregator linked to the theme "@tema" for the breadcrumbs to work properly.', ['@tema' => $parent->getName()]), $messenger::TYPE_WARNING);
              }
            }

            if (isset($aggregatore)) {
              $links[] = Link::createFromRoute($aggregatore->getTitle(), 'entity.node.canonical', ['node' => $aggregatore->id()]);
            }
            if (isset($comune)) {
              $links[] = Link::createFromRoute($comune->getTitle(), 'entity.node.canonical', ['node' => $comune_id]);
            }
          }
          break;

        case 'aggregatore_tema':
          // $comune_id = $node->get('field_comune')->target_id;
          // If (isset($comune_id)) {

          /** @var \Drupal\node\Entity\Node $comune */
          /*
          $comune = Node::load($comune_id);
          $links[] = Link::createFromRoute($comune->getTitle(),
          'entity.node.canonical', ['node' => $comune_id]);
          }*/
          break;

        default:
          break;
      }

      $servizi_ct = [
        'aggregatore_servizi',
        'comune_servizi',
        'scheda_servizio',
        'sala',
      ];

      $label = t('Places');
      if (in_array($node->getType(), $servizi_ct)) {
        $label = t('Services');
      }
      elseif ($node->getType() === 'settore_segnala_me') {
        $label = t('Segnala ME');
      }
    }

    if (isset($label)) {
      $nid = Drupal::service('m_core.default')->getNidComune($node);
      if (isset($nid)) {
        $comune = Node::load($nid);
        $links[] = Link::createFromRoute($comune->getTitle(),
          'entity.node.canonical', ['node' => $nid]);
        $links[] = Link::createFromRoute(t('<span class="breadcrumb-link">@label</span>',
          ['@label' => $label]), 'entity.node.canonical', ['node' => $nid]);

      }
      else {
        $links[] = Link::createFromRoute(
          t('<span class="breadcrumb-link">@label</span> <span class="separator">/</span>',
            ['@label' => $label]), '<nolink>');
      }
    }

    $user = $route_match->getParameter('user');

    if ($user instanceof User) {
      $links[] = Link::createFromRoute(t('Personal area'), '<nolink>');
    }

    $links[] = Link::createFromRoute(t('Home'), '<front>');
    return $links;
  }

}
