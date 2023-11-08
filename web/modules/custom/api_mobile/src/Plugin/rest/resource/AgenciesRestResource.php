<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "agencies_rest_resource",
 *   label = @Translation("Agencies REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/agencies/list"
 *   }
 * )
 */
class AgenciesRestResource extends BaseRestResource {

  const E_NAME = 'agenzia_turistica';
  const CID = 'api_module:agenzia_turistica';

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

    // Get parameters from GET request.
    $quantity   = $this->currentRequest->get('quantity') ?? -1;
    $startIndex = $this->currentRequest->get('startIndex') ?? FALSE;
    // Get denominetion parameter's value.
    $denomination = $this->currentRequest->get('denomination') ?? FALSE;
    $langcode = $this->currentRequest->get('langcode') ?? FALSE;
    $langcodeResponse = $this->setLangcode($langcode, $properties);

    if ($langcodeResponse !== TRUE) {
      return $langcodeResponse;
    }

    if ($denomination) {
      $properties['title'] = $denomination;
    }

    // Execute the entity query.
    $agencies = $this->entityStorage->loadByProperties($properties);
    if (count($agencies) === 0 && $langcode !== Constants::DEFAULT_LANGCODE) {
      $this->setLangcode([$langcode, Constants::DEFAULT_LANGCODE], $properties);
      // Query on entities to get all of given type.
      $agencies = $this->entityStorage->loadByProperties($properties);
    }
    shuffle($agencies);

    $tempAgencies = [];

    foreach ($agencies as $agency) {
      $currentLanguage = $agency->language()->getId();

      if ($currentLanguage !== $langcode) {
        $agency = $this->getTranslation($agency, $currentLanguage, $langcode);
      }
      $this->cacheableMetadata[] = $agency->getCacheTags();

      $emails = $websites = $phones = NULL;

      // Build array of values for field_telefono.
      if ($agency->hasField('field_telefono')) {
        $phones = $agency->get('field_telefono')->getValue();
        $phones = array_map(function ($phone) {
          return $phone['value'];
        }, $phones);
      }

      // Build array of values for field_email.
      if ($agency->hasField('field_email')) {
        $emails = $agency->get('field_email')->getValue();
        $emails = array_map(function ($email) {
          return $email['value'];
        }, $emails);
      }

      // Build array of values for field_sito_web.
      if ($agency->hasField('field_sito_web')) {
        $websites = $agency->get('field_sito_web')->getValue();
        $websites = array_map(function ($website) {
          return $website['value'];
        }, $websites);
      }

      $tempAgencies[] = [
        'name'     => $agency->label(),
        'phones'   => $phones,
        'webSites' => $websites,
        'emails'   => $emails,
      ];
    }
    $agencies = $tempAgencies;
    unset($tempAgencies);

    return $this->prepareResponse($agencies, 'agencies', $quantity, $startIndex);
  }

}
