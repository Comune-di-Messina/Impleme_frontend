<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get cities list.
 *
 * @RestResource(
 *   id = "cities_rest_resource",
 *   label = @Translation("Cities REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/cities/list"
 *   }
 * )
 */
class CitiesRestResource extends BaseRestResource {
  const E_NAME = 'comune';
  const CID = 'api_module:citta';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManager $entityTypeManager, Request $currentRequest, CacheBackendInterface $cacheBackend) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $entityTypeManager, $currentRequest, $cacheBackend);

    $this->cacheId = self::CID;
  }

  /**
   * Get cities list.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get(): ResourceResponse {
    // Query on entities to get all of given type.
    $cities = $this->entityStorage->loadByProperties([
      'type' => self::E_NAME,
      'status' => TRUE,
    ]);

    $tempCitiesArray = [];

    foreach ($cities as $city) {
      $tempCitiesArray[] = $city->label();
      $this->cacheableMetadata[] = $city;
    }

    return $this->prepareResponse($tempCitiesArray, 'cities');
  }

}
