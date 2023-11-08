<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get agencies' languages list.
 *
 * @RestResource(
 *   id = "agencies_languages_rest_resource",
 *   label = @Translation("Agencies' Languages REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/agencies/languages/list"
 *   }
 * )
 */
class AgenciesLanguagesRestResource extends BaseRestResource {
  const E_NAME = 'lingue';
  const CID = 'api_module:agenzia_lingue';

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
   * Get agencies' languages list.
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

    // Get entityStorage for "lingue" vocabulary.
    $languages = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);
    if (count($languages) === 0 && $langcode !== Constants::DEFAULT_LANGCODE) {
      $this->setLangcode([$langcode, Constants::DEFAULT_LANGCODE], $properties);
      // Query on entities to get all of given type.
      $languages = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties($properties);
    }

    // Temporary terms array.
    $tempLanguages = [];

    foreach ($languages as $language) {
      $currentLanguage = $language->language()->getId();

      if ($currentLanguage !== $langcode) {
        $language = $this->getTranslation($language, $currentLanguage, $langcode);
      }

      $this->cacheableMetadata[] = $language->getCacheTags();
      $languageDescription = $language->get('description')->getValue();
      $languageDescription = isset($languageDescription) ? strip_tags(preg_replace('/\s+/', '', $languageDescription[0]['value'])) : NULL;
      $tempLanguages[] = [
        'abbreviation' => $language->label(),
        'name' => $languageDescription,
      ];
    }
    $languages = $tempLanguages;

    return $this->prepareResponse($languages, 'languages');
  }

}
