<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use GuzzleHttp\Exception\ClientException;

/**
 * Provides a resource to get rooms' typologies list.
 *
 * @RestResource(
 *   id = "rooms_typologies_rest_resource",
 *   label = @Translation("Rooms Typologies REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/rooms/typologies/list"
 *   }
 * )
 */
class RoomsTypologiesRestResource extends BaseRestResource {

  /**
   * Get rooms typologies list.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get(): ResourceResponse {
    $properties = [];

    // Get the mandatory langcode parameter's value.
    $langcode = $this->currentRequest->get('langcode') ?? FALSE;
    $langcode = $this->setLangcode($langcode, $properties);

    if ($langcode !== TRUE) {
      return $langcode;
    }

    /** @var \Drupal\m_api\PrenotaMeService $mAPIClient */
    $mAPIClient = \Drupal::service('m_api.prenotame_client');
    $tempTypes  = [];

    try {
      $types = $mAPIClient->getTipiStrutture();
      foreach ($types as $id => $type) {
        $tempTypes[] = [
          'label' => $type,
          'id' => $id,
        ];
      }
    }
    catch (ClientException $exception) {
      return new ResourceResponse([$exception->getCode() => $exception->getMessage()]);
    }

    return new ResourceResponse(['types' => $tempTypes]);
  }

}
