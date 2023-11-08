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
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "aggregators_rest_resource",
 *   label = @Translation("Aggregators REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/aggregators/list"
 *   }
 * )
 */
class AggregatorsRestResource extends BaseRestResource {
  const E_TYPE = 'node';
  const E_NAME = 'aggregatore_tema';
  const CID = 'api_module:aggregatori';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManager $entityTypeManager, Request $currentRequest, CacheBackendInterface $cacheBackend) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $entityTypeManager, $currentRequest, $cacheBackend);

    $this->entityStorage = $this->entityTypeManager->getStorage(self::E_TYPE);
    $this->cacheId = self::CID;
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  public function get(): ResourceResponse {
    // Array that will contains all aggregators and relative themes.
    $aggregators = [];
    // Entity query's parameters.
    $parameters = ['type' => self::E_NAME, 'status' => TRUE];

    $langcode = $this->currentRequest->get('langcode');
    $langcodeResponse = $this->setLangcode($langcode, $parameters);

    if ($langcodeResponse !== TRUE) {
      return $langcodeResponse;
    }

    if ($city = $this->currentRequest->get('city')) {
      // If city value passed check's for its ID to use in loadByProperties.
      $city = $this->getNodeIdByTitle('comune', $city);

      // If there's exclusively one value in cityNode array means that
      // we found the passed city in 'comune' nodes list.
      if (!$city) {
        return new ResourceResponse(['Error occurred during city loading. Check the city\'s name and retry.'], 501);
      }
      else {
        $parameters['field_comune'] = $city;
      }

      // Load all aggregatore_tema afferent to city id.
      $entities = $this->entityStorage->loadByProperties($parameters);

      // Count index.
      $currentIdx = 0;

      // Setup term storage to get term's childrens.
      $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

      // Loop through all entities (aggregators) to load all themes.
      foreach ($entities as $entity) {
        $currentLanguage = $entity->language()->getId();

        if ($currentLanguage !== $langcode) {
          $entity = $entity->getTranslation($langcode);
        }

        $this->cacheableMetadata[] = $entity->getCacheTags();

        if ($entity->field_cover) {
          $uri = isset($entity->field_cover->entity) ? $entity->field_cover->entity->get('field_media_image')->entity->uri->getValue() : NULL;
          $uri = ImageStyle::load('large')->buildUrl($uri[0]['value']);
        }
        else {
          $uri = '';
        }
        $aggregators[$currentIdx] = [
          'idAggregator' => $entity->id(),
          'title' => $entity->label(),
          'image' => file_url_transform_relative(file_create_url($uri)),
        ];

        // Get current $entity's tema value.
        $parentTheme = $entity->hasField('field_term_tema') ? $entity->field_term_tema->getValue()[0]['target_id'] : NULL;
        $childThemes = $termStorage->loadTree('temi', (int) $parentTheme);
        // Map array to only have [index -> tid].
        $childThemes = array_map(function ($item) {
          return $item->tid;
        }, $childThemes);

        // Temp themes use to distinct duplications.
        $tempThemes = [];

        // Search all nodes in field_comune with field_term_temi IN
        // ($childThemes). i.e. tagged with one of $childThemes item.
        if (count($childThemes) > 0) {
          $relatedNodes = $this->entityStorage->loadByProperties([
            'field_comune' => $city,
            'type' => Constants::POI_TYPES,
            'field_term_temi' => $childThemes,
          ] + ['langcode' => [$langcode], 'default_langcode' => NULL]);

          if (!empty($relatedNodes)) {
            foreach ($relatedNodes as $theme) {
              $theme = $theme->getTranslation($langcode);
              $idTheme = $theme->field_term_temi->entity->id();
              $currentLanguage = $theme->language()->getId();

              if ($theme->field_term_temi->entity->getTranslationStatus($langcode)) {
                $titleTheme = $theme->field_term_temi->entity->getTranslation($langcode)->label();
              }
              else {
                $titleTheme = $theme->field_term_temi->entity->label();
              }

              $tempThemes[$theme->field_term_temi->entity->label()] = [
                'idTheme' => $idTheme,
                'title' => $titleTheme,
              ];
            }
          }
          else {
            unset($aggregators[$currentIdx]);
            continue;
          }

          // Pass only array's values to 'themes' key.
          $aggregators[$currentIdx]['themes'] = array_values($tempThemes);
        }
        else {
          unset($aggregators[$currentIdx]);
          $currentIdx -= 1;
        }

        $currentIdx++;
      }

    }
    else {
      return new ResourceResponse(['error' => 'No city parameter passed.'], 501);
    }

    return $this->prepareResponse($aggregators, 'aggregators');
  }

}
