<?php

namespace Drupal\api_mobile\Plugin\rest\resource;

use Drupal\api_mobile\Constants;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get cities list.
 *
 * @RestResource(
 *   id = "guides_rest_resource",
 *   label = @Translation("Guides REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/guides/list"
 *   }
 * )
 */
class GuidesRestResource extends BaseRestResource {
  const E_NAME = 'guida_turistica';
  const CID = 'api_module:guida_turistica';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManager $entityTypeManager, Request $currentRequest, CacheBackendInterface $cacheBackend) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $entityTypeManager, $currentRequest, $cacheBackend);

    $this->cacheId = self::CID;
  }

  /**
   * Get guides list.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function get() {
    // Properties used to get drupal's entities.
    $properties = ['type' => self::E_NAME, 'status' => TRUE];
    // Get the mandatory langcode parameter's value.
    $langcode = $this->currentRequest->get('langcode') ?? FALSE;
    $langcodeResponse = $this->setLangcode($langcode, $properties);

    if ($langcodeResponse !== TRUE) {
      return $langcodeResponse;
    }

    // Get a taxonomy storage.
    $taxonomyStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Get optional quantity parameter's value.
    $quantity = $this->currentRequest->get('quantity');
    // Get optional startIndex parameter's value.
    $startIndex = $this->currentRequest->get('startIndex');
    // Get optional city parameter's value.
    $city = $this->currentRequest->get('city');
    // Get optional residence parameter's value.
    $residence = $this->currentRequest->get('residence');
    // Get optional language parametr's value.
    $language = $this->currentRequest->get('language');

    // If $city is setted get comune's node id from $city's name value.
    if ($city) {
      $cityId = $this->getNodeIdByTitle('comune', $city);

      if (!$cityId) {
        return $this->error('Zero or multiple cities were found with the given name. Fix the exception and try again.');
      }
      else {
        $properties['field_comune'] = $cityId;
      }
      // Create a CacheID key with <self::CID>:<cityName>.
      $this->cacheId = $this->cacheId . ':' . $city;
    }
    else {
      // Create a CacheID key with <self::CID>:all.
      $this->cacheId = $this->cacheId . ':all';
    }

    if ($residence) {
      $residenceTerm = $this->getTermIdFromName('residenze', $residence);

      if ($residenceTerm !== FALSE) {
        $properties['field_residenza'] = $residenceTerm;

        // Append to the cacheId variable also the $residence parameter's value.
        $this->cacheId .= ':' . $residence;
      }
      else {
        return $this->error('Zero or multiple residences were found with the given name. Fix the exception and try again.');
      }
    }

    if ($language) {
      $languages = $taxonomyStorage->loadByProperties([
        'vid' => 'lingue',
        'name' => $language,
      ]);
      foreach ($languages as $language) {
        $properties['field_lingua'] = $language->id();
      }
    }

    // If already exists cached guides use they.
    if ($guides = $this->cacheBackend->get($this->cacheId)) {
      shuffle($guides->data);
      $tempGuides = $guides->data;
    }
    else {
      // Otherwise get all guides and then cache they.
      $guides = $this->entityStorage->loadByProperties($properties);
      if (count($guides) === 0 && $langcode !== Constants::DEFAULT_LANGCODE) {
        $this->setLangcode([$langcode, Constants::DEFAULT_LANGCODE], $properties);
        // Query on entities to get all of given type.
        $guides = $this->entityStorage->loadByProperties($properties);
      }
      shuffle($guides);

      // This will contains all remapped guides.
      $tempGuides = [];

      /** @var \Drupal\node\Entity\Node $guide */
      foreach ($guides as $guide) {
        $currentLanguage = $guide->language()->getId();

        if ($currentLanguage !== $langcode) {
          $guide = $this->getTranslation($guide, $currentLanguage, $langcode);
        }

        $this->cacheableMetadata[] = $guide->getCacheTags();

        $emails = $websites = $phones = NULL;

        // Build array of values for field_telefono.
        if ($guide->hasField('field_telefono')) {
          $phones = $guide->get('field_telefono')->getValue();
          $phones = array_map(function ($phone) {
            return $phone['value'];
          }, $phones);
        }

        // Build array of values for field_email.
        if ($guide->hasField('field_email')) {
          $emails = $guide->get('field_email')->getValue();
          $emails = array_map(function ($email) {
            return $email['value'];
          }, $emails);
        }

        // Build array of values for field_sito_web.
        if ($guide->hasField('field_sito_web')) {
          $websites = $guide->get('field_sito_web')->getValue();
          $websites = array_map(function ($website) {
            return $website['value'];
          }, $websites);
        }

        $tempGuide = [
          'name'      => $guide->label(),
          'phones'    => $phones,
          'residence' => $guide->get('field_residenza') ? $guide->get('field_residenza')->entity->getName() : NULL,
          'websites'  => $websites,
          'emails'    => $emails,
        ];
        $languages = $guide->get('field_lingua')->getValue();

        // Loop through all languages.
        foreach ($languages as $language) {
          $language = $taxonomyStorage->load($language['target_id'])->getName();
          $tempGuide['languages'][] = $language;
        }
        $tempGuides[] = $tempGuide;
      }
    }

    return $this->prepareResponse($tempGuides, 'guides', $quantity, $startIndex);
  }

}
