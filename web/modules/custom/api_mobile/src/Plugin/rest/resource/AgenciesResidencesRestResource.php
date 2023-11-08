<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get agencies' residences list.
 *
 * @RestResource(
 *   id = "agencies_residences_rest_resource",
 *   label = @Translation("Agencies' Residences REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/agencies/residences/list"
 *   }
 * )
 */
class AgenciesResidencesRestResource extends BaseRestResource {
  const E_NAME = 'residenze';
  const CID = 'api_module:agenzia_residenze';

  /**
   * AgenciesLanguagesRestResource constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManager $entityTypeManager, Request $currentRequest, CacheBackendInterface $cacheBackend) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $entityTypeManager, $currentRequest, $cacheBackend);

    $this->cacheId = self::CID;
  }

  /**
   * Get agencies' residences list.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get(): ResourceResponse {
    $properties = ['status' => TRUE];

    // Get mandatory langcode from GET parameters.
    $langcode = $this->currentRequest->get('langcode') ?? FALSE;
    // Check for langcode, prepare cacheId with langcode suffix.
    $langcodeResponse = $this->setLangcode($langcode, $properties);

    if ($langcodeResponse !== TRUE) {
      return $langcodeResponse;
    }

    // Entities' properties.
    $properties['vid'] = self::E_NAME;

    // Get entityStorage for "residenze" vocabulary.
    $residences = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);
    if (count($residences) === 0 && $langcode !== Constants::DEFAULT_LANGCODE) {
      $this->setLangcode([$langcode, Constants::DEFAULT_LANGCODE], $properties);
      // Query on entities to get all of given type.
      $residences = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);
    }

    // Temporary terms array.
    $tempLanguages = [];

    foreach ($residences as $language) {
      $currentLanguage = $language->language()->getId();

      if ($currentLanguage !== $langcode) {
        $language = $this->getTranslation($language, $currentLanguage, $langcode);
      }

      $this->cacheableMetadata[] = $language->getCacheTags();

      $tempLanguages[] = [
        'id' => $language->id(),
        'name' => $language->label(),
      ];
    }
    $residences = $tempLanguages;

    return $this->prepareResponse($residences, 'residences');
  }

}
