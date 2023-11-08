<?php

namespace Drupal\api_mobile;

/**
 * Constant class.
 */
class Constants {
  const DEFAULT_LANGCODE = 'it';
  const POI_TYPES = ['evento', 'scheda', 'itinerario'];

  const VIEWS_NAMES = [
    'api_mobile',
    'api_mobile_taxonomy',
  ];

  // Points Of Interest.
  const VIEW_NAME_POI_LIST = 'rest_export__poi_list';
  const VIEW_NAME_POI_DETAIL = 'rest_export__poi_detail';

  // Aggregators.
  const VIEW_NAME_AGGREGATORS_LIST = 'rest_export__aggregators_list';

  // Rooms.
  const VIEW_NAME_ROOMS_LIST = 'rest_export__rooms_list';
  const VIEW_NAME_ROOMS_DETAIL = 'rest_export__rooms_detail';

  const VIEW_OPTIONS = [
    self::VIEW_NAME_POI_LIST => [
      'collectionName' => 'items',
      'hasNextIndex' => TRUE,
    ],
    self::VIEW_NAME_POI_DETAIL => [
      'hasNextIndex' => FALSE,
    ],
    self::VIEW_NAME_AGGREGATORS_LIST => [
      'collectionName' => 'aggregators',
      'hasNextIndex' => TRUE,
    ],
    self::VIEW_NAME_ROOMS_LIST => [
      'collectionName' => 'rooms',
      'hasNextIndex' => TRUE,
    ],
    self::VIEW_NAME_ROOMS_DETAIL => [
      'hasNextIndex' => FALSE,
    ],
  ];


  // Required parameters.
  const MANDATORY_PARAMETERS = [
    self::VIEW_NAME_POI_LIST => [
      'city',
      'type',
      'idThemes',
      'langcode',
    ],

    self::VIEW_NAME_POI_DETAIL => [
      'itemId',
      'langcode',
    ],

    self::VIEW_NAME_AGGREGATORS_LIST => [
      'city',
      'langcode',
    ],

    self::VIEW_NAME_ROOMS_LIST => [
      'city',
      'langcode',
    ],

    self::VIEW_NAME_ROOMS_DETAIL => [
      'id',
      'langcode',
    ],
  ];

  const ERRORS = [
    'NO_MANDATORY_FIELDS' => [
      'code' => 400,
      'type' => 'Bad Request',
      'message' => 'Required parameters missing.',
    ],
  ];

  const ALLOWED_PARAMETERS_VALUE = [
    'global' => [
      'langcode' => ['it', 'en', 'all'],
    ],
    self::VIEW_NAME_POI_LIST => [
      'type' => ['evento', 'scheda', 'itinerario'],
    ],
  ];

}
