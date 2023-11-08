<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Cache\CacheBackendInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Drupal\api_mobile\Constants as ApiMobileConstants;

/**
 * Class BaseRestResource.
 *
 * @package Drupal\api_mobile\Plugin\rest\resource
 */
class BaseRestResource extends ResourceBase {
  const ENTITY_TYPE_BASE = 'node';

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageBase
   */
  protected $entityStorage;

  /**
   * Current request from stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Cache ID.
   *
   * @var string
   */
  protected $cacheId;

  /**
   * Cache metadata [tags].
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheableMetadata;

  /**
   * Cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Annotation Reader.
   *
   * @var \Doctrine\Common\Annotations\AnnotationReader
   */
  protected $annotationReader;

  /**
   * Taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $taxonomyStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManager $entityTypeManager,
    Request $currentRequest,
    CacheBackendInterface $cacheBackend
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $serializer_formats,
      $logger
    );

    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage(self::ENTITY_TYPE_BASE);
    $this->currentRequest = $currentRequest;
    $this->cacheBackend = $cacheBackend;
    $this->cacheableMetadata = NULL;
    $this->annotationReader = new AnnotationReader();
    $this->taxonomyStorage = $this->entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('api_mobile'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('cache.default')
    );
  }

  /**
   * Prepare a cached response object.
   *
   * @param mixed $data
   *   Data to return as response.
   * @param string $rootKey
   *   Optional data's root key.
   * @param int|null $quantity
   *   Optional number of values to return.
   * @param int|null $startIndex
   *   Optional offset from which to start.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  protected function prepareResponse($data, string $rootKey = NULL, int $quantity = NULL, int $startIndex = NULL): ResourceResponse {
    if ($cached = $this->cacheBackend->get($this->cacheId)) {
      $data = $cached->data;
      if ($rootKey) {
        $data = $data[$rootKey];
      }
    }

    if ($rootKey) {
      $data = [$rootKey => $data];
    }

    if (is_int($quantity) && $quantity > 0) {
      $tempData = $rootKey ? $data[$rootKey] : $data;
      $totalData = count($tempData);
      $startIndex = $startIndex ? $startIndex : 0;

      // Remap cacheableMetadata array.
      if (is_array($this->cacheableMetadata)) {
        $this->cacheableMetadata = array_map(function ($item) {
          return reset($item);
        }, $this->cacheableMetadata);
      }

      // Cache guides.
      if (!$cached) {
        $this->cacheBackend->set($this->cacheId, $data, Cache::PERMANENT, $this->cacheableMetadata);
      }

      // Extract an array's subset.
      $tempData = array_slice($tempData, $startIndex, $quantity, TRUE);
      $data = $rootKey ? [$rootKey => $tempData] : $tempData;

      // Calculates the nextIndex value.
      $nextIndex = $totalData - ($totalData - $quantity) + ($startIndex ? $startIndex : 0);

      if ($quantity >= $totalData || $nextIndex > ($totalData - $quantity)) {
        $nextIndex = -1;
      }
      else {
        $nextIndex = $nextIndex <= 0 ? -1 : $nextIndex;
      }

      $data['nextIndex'] = $nextIndex;
    }
    else {
      if ($quantity === -1) {
        $data['nextIndex'] = -1;
      }
    }

    return new ResourceResponse($data);
  }

  /**
   * Create and return an error message.
   *
   * @param mixed $message
   *   Error message.
   * @param int $code
   *   Error code.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  protected function error($message, int $code = 501) : ResourceResponse {
    return new ResourceResponse(
      [
        'error' => $message,
        'code' => $code,
      ]
    );
  }

  /**
   * Get taxonomy term's ID from given name.
   *
   * @param string $vid
   *   Vocabulary ID.
   * @param string $name
   *   Term name.
   *
   * @return \Drupal\rest\ResourceResponse|bool
   *   Obtained ID or false.
   */
  protected function getTermIdFromName(string $vid, string $name) {
    $term = $this->taxonomyStorage->loadByProperties([
      'vid'  => $vid,
      'name' => $name,
    ]);

    if (count($term) === 1) {
      $term = is_array($term) ? reset($term)->id() : FALSE;

      // If term is not FALSE append to cacheId the ':$name' string.
      if ($term) {
        $this->cacheId = ":$name";
      }

      return $term;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get entity's ID from given title.
   *
   * @param string $type
   *   Entity type.
   * @param string $title
   *   Entity title.
   *
   * @return false|int|string|null
   *   Obtained ID or false.
   */
  protected function getNodeIdByTitle(string $type, string $title) {
    $node = $this->entityStorage->loadByProperties([
      'type' => $type,
      'title' => strtolower($title),
    ]);

    if (count($node) === 1) {
      $node = is_array($node) ? reset($node)->id() : FALSE;

      // If term is not FALSE append to cacheId the ':$title' string.
      if ($node) {
        $this->cacheId .= ":$title";
      }

      return $node;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setLangcode($langcode, &$properties) {
    if (!$langcode) {
      return new ResourceResponse([
        'error' => Constants::ERRORS['NO_MANDATORY_FIELDS'],
        'missing_parameters' => 'langcode',
        'allowed_values' => Constants::ALLOWED_PARAMETERS_VALUE['global'],
      ], 501);
    }
    else {
      if (!is_array($langcode)) {
        $cacheKey = $langcode;
        $langcode = [$langcode];
        $properties['default_langcode'] = NULL;
      }
      else {
        $properties['default_langcode'] = TRUE;
        $cacheKey = implode('-', $langcode);
      }
      $properties['langcode'] = $langcode;

      $this->cacheId .= ":$cacheKey";

      return TRUE;
    }
  }

  /**
   * If exists use translated node version.
   *
   * @param mixed $entity
   *   Entity object.
   * @param mixed $currentLanguage
   *   Entity language.
   * @param mixed $langcode
   *   Required language.
   *
   * @return mixed
   *   Entity translation.
   */
  protected function getTranslation($entity, $currentLanguage, $langcode) {
    try {
      $entity = $entity->getTranslation($langcode);
    }
    catch (\InvalidArgumentException $exception) {
      $entity = $entity;
    }

    return $entity;
  }

  /**
   * Validates parameters.
   */
  protected function isValid() {
    $hasError = FALSE;
    $error = ApiMobileConstants::ERRORS['NO_MANDATORY_FIELDS'];

    foreach ($this::MANDATORY_PARAMETERS as $paramName => $paramOptions) {
      if (!$this->currentRequest->get($paramName)) {
        if (isset($error['required_parameters'][$paramName]['allowed_values'])) {
          $error['required_parameters'][$paramName]['allowed_values'] = $paramOptions['allowed_values'];
        }

        $error['required_parameters'][$paramName]['current_value'] = NULL;

        $hasError = TRUE;
      }
    }

    return $hasError ? $error : TRUE;
  }

}
