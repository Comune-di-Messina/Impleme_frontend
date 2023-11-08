<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "agencies_names_rest_resource",
 *   label = @Translation("Agencies REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/agencies/denominations/list"
 *   }
 * )
 */
class AgenciesNamesRestResource extends BaseRestResource {

  const E_NAME = 'agenzia_turistica';
  const CID = 'api_module:agenzia_turistica_nomi';

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function get() {
    // Set entity query's properties.
    $properties = [
      'type' => self::E_NAME,
      'status' => TRUE,
    ];

    $langcode = $this->currentRequest->get('langcode') ?? FALSE;
    $langcodeResponse = $this->setLangcode($langcode, $properties);

    if ($langcodeResponse !== TRUE) {
      return $langcodeResponse;
    }

    // Execute the entity query.
    $agencies = $this->entityStorage->loadByProperties($properties);
    if (count($agencies) === 0 && $langcode !== Constants::DEFAULT_LANGCODE) {
      $this->setLangcode([$langcode, Constants::DEFAULT_LANGCODE], $properties);
      // Query on entities to get all of given type.
      $agencies = $this->entityStorage->loadByProperties($properties);
    }

    $tempAgencies = [];

    foreach ($agencies as $agency) {
      $currentLanguage = $agency->language()->getId();

      if ($currentLanguage !== $langcode) {
        $agency = $this->getTranslation($agency, $currentLanguage, $langcode);
      }

      $this->cacheableMetadata[] = $agency->getCacheTags();

      $tempAgencies[] = [
        'id'   => $agency->id(),
        'name' => $agency->label(),
      ];
    }
    $agencies = $tempAgencies;
    unset($tempAgencies);

    return $this->prepareResponse($agencies, 'denominations');
  }

}
