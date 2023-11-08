<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\rest\ResourceResponse;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get rooms list.
 *
 * @RestResource(
 *   id = "rooms_rest_resource",
 *   label = @Translation("Rooms REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/rooms/list"
 *   }
 * )
 */
class RoomsRestResource extends BaseRestResource {
  const E_NAME = 'sala';
  const CID = 'api_module:sala';

  const MANDATORY_PARAMETERS = [
    'langcode' => [
      'parameter_name' => 'langcode',
      'allowed_values' => Constants::ALLOWED_PARAMETERS_VALUE['global']['langcode'],
    ],
    'city' => ['parameter_name' => 'city'],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManager $entityTypeManager, Request $currentRequest, CacheBackendInterface $cacheBackend) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $entityTypeManager, $currentRequest, $cacheBackend);
    $this->cacheId = self::CID;
  }

  /**
   * Get rooms list.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get(): ResourceResponse {
    // Get default langcode id.
    $defaultLang = Constants::DEFAULT_LANGCODE;
    // Get optional quantity parameter's value.
    $quantity = $this->currentRequest->get('quantity');
    // Get optional startIndex parameter's value.
    $startIndex = $this->currentRequest->get('startIndex');
    // Get optional city parameter's value.
    $city = $this->currentRequest->get('city');
    // Get optional type parameter's value.
    $type = $this->currentRequest->get('type');

    $properties['type'] = self::E_NAME;
    $properties['status'] = TRUE;

    // Check if isValid.
    $isValid = $this->isValid();
    if ($isValid !== TRUE) {
      return new ResourceResponse($isValid, 501);
    }
    else {
      // If $city is setted get comune's node id from $city's name value.
      $cityId = $this->getNodeIdByTitle('comune_servizi', $city);

      if (!$cityId) {
        return $this->error(
          'Zero or multiple cities were found with the given name. Fix the exception and try again.'
        );
      }
      else {
        $properties['field_comune_servizi'] = $cityId;
      }
      // Create a CacheID key with <self::CID>:<cityName>.
      $this->cacheId = $this->cacheId . ':' . $city;

      // Get the mandatory langcode parameter's value.
      $langcode = $this->currentRequest->get('langcode') ?? FALSE;
      $this->setLangcode($langcode, $properties);

      // Query on entities to get all of given type.
      $rooms = $this->entityStorage->loadByProperties($properties);

      if (count($rooms) === 0 && $langcode !== $defaultLang) {
        $this->setLangcode([$langcode, $defaultLang], $properties);
        // Query on entities to get all of given type.
        $rooms = $this->entityStorage->loadByProperties($properties);
      }

      $tempRoomsArray = [];
      $index = 0;

      foreach ($rooms as $room) {
        $currentLanguage = $room->language()->getId();

        if ($currentLanguage !== $langcode) {
          $room = $this->getTranslation($room, $currentLanguage, $langcode);
        }

        $roomId = $room->get('field_room_id')->getValue();

        $tempRoomsArray[$index] = [
          'title' => $room->label(),
          'id' => isset($roomId) ? $roomId[0]['value'] : NULL,
        ];

        /** @var \Drupal\m_api\PrenotaMeService $mAPIClient */
        $mAPIClient = \Drupal::service('m_api.prenotame_client');
        try {
          $roomFromBOLite = $mAPIClient->getDettagliSala($roomId[0]['value']);
          $tempRoomsArray[$index]['capacity'] = $roomFromBOLite->capienza;
          if (isset($roomFromBOLite->tipoStruttura)) {
            $types = $mAPIClient->getTipiStrutture();
            if (in_array($roomFromBOLite->tipoStruttura, array_keys($types))) {
              $tempRoomsArray[$index]['type'] = $types[$roomFromBOLite->tipoStruttura];
            }
          }
        }
        catch (ClientException $exception) {
        }

        if ($room->hasField('field_cover')) {
          if (isset($room->field_cover->entity)) {
            $uri = $room->field_cover->entity->get(
              'field_media_image'
            )->entity->uri->getValue();
            $roomImage = ImageStyle::load('large')
              ->buildUrl($uri[0]['value']);
            $tempRoomsArray[$index]['image'] = file_url_transform_relative($roomImage);
          }
          else {
            $tempRoomsArray[$index]['image'] = NULL;
          }
        }

        $tempRoomsArray[$index]['description'] = !empty($room->body->getValue()) ? $room->body->getValue()[0]['value'] : NULL;

        $tempRoomsArray[$index]['place']['name'] = !empty($room->get('field_contatti_titolo')->getValue()) ? $room->get('field_contatti_titolo')->getValue()[0]['value'] : NULL;
        $tempRoomsArray[$index]['place']['address'] = !empty($room->get('field_contatti_indirizzo')->getValue()) ? $room->get('field_contatti_indirizzo')->getValue()[0]['value'] : NULL;

        $coordinate = $room->get('field_coordinate')->getValue();

        if (!empty($coordinate)) {
          $tempRoomsArray[$index]['place']['latitude'] = $coordinate[0]['lat'];
          $tempRoomsArray[$index]['place']['longitude'] = $coordinate[0]['lon'];
        }

        $this->cacheableMetadata[] = $room;
        $index++;
      }

      if ($type) {
        foreach ($tempRoomsArray as $index => $room) {
          $type = trim(strtolower($type));
          $roomType = trim(strtolower($room['type']));
          if ($roomType !== $type) {
            unset($tempRoomsArray[$index]);
          }
        }
        $tempRoomsArray = array_values($tempRoomsArray);
      }

      return $this->prepareResponse($tempRoomsArray, 'rooms', $quantity, $startIndex);
    }
  }

}
