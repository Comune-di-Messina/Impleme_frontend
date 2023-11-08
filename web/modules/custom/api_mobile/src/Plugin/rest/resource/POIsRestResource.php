<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\image\Entity\ImageStyle;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get points of interest list.
 *
 * @RestResource(
 *   id = "pois_rest_resource",
 *   label = @Translation("POIs REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/poi/list"
 *   }
 * )
 */
class POIsRestResource extends BaseRestResource {
  const E_NAME = Constants::POI_TYPES;
  const CID = 'api_module:punti_di_interesse';
  // Constant array of mandatory parameters,
  // with some useful fix suggestions.
  const MANDATORY_PARAMETERS = [
    'type' => [
      'parameter_name' => 'type',
      'allowed_values' => self::E_NAME,
    ],
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
   * Get POIs list.
   */
  public function get(): ResourceResponse {
    // Get parameters from GET request.
    $quantity   = $this->currentRequest->get('quantity') ?? -1;
    $startIndex = $this->currentRequest->get('startIndex') ?? FALSE;
    $promoted   = $this->currentRequest->get('inevidenza') ?? FALSE;

    $isValid = $this->isValid();

    if ($isValid !== TRUE) {
      return new ResourceResponse($isValid, 400);
    }
    else {
      $type = $this->currentRequest->get('type');
      $city = $this->currentRequest->get('city');
      $cityId = $this->getNodeIdByTitle('comune', $city);
      $idThemes = $idThemesBackup = $this->currentRequest->get('idThemes');
      // Set cacheId using $type.
      $this->cacheId .= ":$type";

      if ($idAggregator = $this->currentRequest->get('idAggregator')) {
        $this->cacheId .= ':' . $idAggregator;

        $aggregator = $this->entityTypeManager->getStorage('node')->load($idAggregator);
        if ($aggregator->get('field_comune')->entity->id() == $cityId) {
          if ($aggregator->hasField('field_term_tema')) {
            $idAggregator = $aggregator->get('field_term_tema')->getValue();
            $idAggregator = $idAggregator[0]['target_id'];
          }
        }

        if (empty($idThemes)) {
          $idThemes = $this->taxonomyStorage->loadChildren($idAggregator);
          $idThemes = array_keys($idThemes);
        }

        foreach ($idThemes as $key => $idTheme) {
          $termParents = $this->entityTypeManager->getStorage('taxonomy_term')->loadParents($idTheme);

          // Map array.
          $termParents = array_map(function ($term) {
            return $term->id();
          }, $termParents);

          if (!in_array($idAggregator, $termParents)) {
            unset($idThemes[$key]);
          }
          else {
            $this->cacheId .= ":$idTheme";
          }
        }
      }
      else {
        foreach ($idThemes as $key => $idTheme) {
          $this->cacheId .= ":$idTheme";
        }
      }

      $pois = [];

      $langcode = $this->currentRequest->get('langcode');
      $entitiesLanguage = $langcode;
      if ($entitiesLanguage !== Constants::DEFAULT_LANGCODE) {
        $entitiesLanguage = [$entitiesLanguage, Constants::DEFAULT_LANGCODE];
        $langOperator = 'IN';
      }
      else {
        $langOperator = '=';
      }

      $entities = \Drupal::entityQuery(self::ENTITY_TYPE_BASE)
        ->condition('type', $type)
        ->condition('status', TRUE)
        ->condition('langcode', $entitiesLanguage, $langOperator)
        ->condition('field_comune', $cityId)
        ->sort('created', 'DESC');

      if ($promoted) {
        $entities->condition('promote', TRUE);
        $this->cacheId .= ':promote';
      }

      if (count($idThemes) > 0) {
        $entities->condition('field_term_temi', $idThemes, 'IN');
      }
      else {
        if (count($idThemesBackup) > 0) {
          foreach ($idThemesBackup as $idTheme) {
            $entities->condition('field_term_temi', $idTheme, '=');
          }
        }
      }
      if ($startDate = $this->currentRequest->get('startDate')) {
        $entities->condition('field_data.value', $startDate, '>=');
      }
      if ($endDate = $this->currentRequest->get('endDate')) {
        $entities->condition('field_data.value', $endDate, '<');
      }

      // Executes the query.
      $entities = $entities->execute();

      foreach ($entities as $entity) {
        // Load the entity using the $entity variable,
        // wich contains the entity's id.
        $entity = $this->entityStorage->load($entity);
        $currentLanguage = $entity->language()->getId();

        if ($entitiesLanguage !== Constants::DEFAULT_LANGCODE) {
          $entity = $this->getTranslation($entity, $currentLanguage, $langcode);
        }
        // Set cacheableMetadata.
        $this->cacheableMetadata[] = $entity->getCacheTags();

        if ($entity->field_cover) {
          $uri = $entity->field_cover->entity->get('field_media_image')->entity->uri->getValue();
          $uri = ImageStyle::load('large')->buildUrl($uri[0]['value']);
          $uri = file_url_transform_relative(file_create_url($uri));
        }
        else {
          $uri = '';
        }

        $description = $entity->get('body')->getValue();
        $date = $entity->hasField('field_data') ? $entity->get('field_data')->getValue() : FALSE;
        // Get dates.
        if ($date) {
          $startDate = $date[0]['value'];
          $endDate   = $date[0]['end_value'];
        }
        else {
          $startDate = NULL;
          $endDate = NULL;
        }

        // Prepare the response array.
        $pois[] = [
          'id' => $entity->id(),
          'imageUrl' => $uri,
          'title'    => $entity->label(),
          'description' => $description[0]['value'],
          'type'        => $entity->getType(),
          'startDate' => $startDate,
          'endDate'   => $endDate,
          'address'   => $entity->hasField('field_contatti_indirizzo') ? $entity->get('field_contatti_indirizzo')->getValue()[0]['value'] : NULL,
          'latitude'  => $entity->hasField('field_coordinate') ? $entity->get('field_coordinate')->getValue()[0]['lat'] : NULL,
          'longitude' => $entity->hasField('field_coordinate') ? $entity->get('field_coordinate')->getValue()[0]['lon'] : NULL,
        ];
      }

      return $this->prepareResponse($pois, 'items', $quantity, $startIndex);
    }
  }

}
