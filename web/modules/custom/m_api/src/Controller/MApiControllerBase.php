<?php

namespace Drupal\m_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\m_api\MClientService;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class MApiControllerBase.
 */
class MApiControllerBase extends ControllerBase {

  /**
   * API Service.
   *
   * @var \Drupal\m_api\MClientService
   */
  protected $apiService;

  /**
   * RouteMatchInterface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * StateInterface.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateService;

  /**
   * Current ente.
   *
   * @var string
   */
  protected $ente = 'SIF07';

  /**
   * Default constructor.
   *
   * @param \Drupal\m_api\MClientService $apiService
   *   The API Service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(MClientService $apiService, RouteMatchInterface $routeMatch, StateInterface $state) {
    $this->apiService = $apiService;
    $this->routeMatch = $routeMatch;
    $this->stateService = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('m_api.m_client'),
      $container->get('current_route_match'),
      $container->get('state')
    );
  }

  /**
   * Basic render function.
   */
  public function render($value) {
    if (is_a($value, 'Exception')) {
      $markup = $value->getMessage();
    }
    else {
      $markup = print_r($value, TRUE);
    }
    $logger = $this->getLogger('m_api');
    $logger->error($markup);
    $response = new RedirectResponse(Url::fromUserInput('/servizi/ko')->toString());
    return $response;
  }

  /**
   * Get Breadcrumbs to be moved to another section.
   *
   * @return array
   *   The links array.
   */
  protected function getBreadcrumb() {
    $breadcrumb = new Breadcrumb();

    $access = \Drupal::accessManager()
      ->check($this->routeMatch, $this->currentUser, NULL, TRUE);

    $breadcrumb->addCacheableDependency($access);
    $breadcrumb->addCacheContexts(['url.path']);

    $links = $this->getLinks($this->routeMatch);
    $links = $breadcrumb->setLinks(array_reverse($links));
    return $links->toRenderable();
  }

  /**
   * Create breadcrumbs links.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   *
   * @return array
   *   An array of links.
   */
  private function getLinks(RouteMatchInterface $route_match) {
    /**
     * @var \Symfony\Component\Routing\Route
     */
    $route = $route_match->getRouteObject();
    if ($route) {
      $title = $route->getDefault('_title');
      $links[] = Link::createFromRoute(
        t(
          '<span class="breadcrumb-item active">@title</span></span>',
          ['@title' => $title]
        ),
        '<nolink>'
      );
    }
    $links[] = Link::createFromRoute(t('<span class="breadcrumb-link">Area personale</span> <span class="separator">/</span>'), '<nolink>');
    $links[] = Link::createFromRoute(t('Home'), '<front>');

    return $links;
  }

  /**
   * Returns a base url without arguments.
   *
   * To be used with page-switcher-select library.
   *
   * @param string $current
   *   Current parameter if present.
   *
   * @return string
   *   Base url for the current path.
   */
  protected function serviceUrl(string $current = '') {
    $query = \Drupal::request()->getQueryString();
    $uri = str_replace($current, '', \Drupal::request()->getRequestUri());
    $uri = str_replace('?&', '?', $uri);
    return rtrim($uri, '/?&');
  }

}
