<?php

namespace Drupal\segnalame\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * SegnalaMe Controller.
 */
class SegnalaMeController extends ControllerBase {
  const CATEGORY = 'Segnala ME';
  const TOKEN    = '5cbdc2c29841ad0c2850501bddef1c948a6bb2c25e017f6ea5bf90dd2ac99fec';

  /**
   * HTTP Client.
   *
   * @var \GuzzleHttp\Clientguzzlehttpclient
   */

  protected $httpClient;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerentitytypemanager
   */

  protected $entityTypeManager;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\Messengerdrupalmessenger
   */

  protected $messenger;

  /**
   * Alias Cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerpathautoaliascleaner
   */

  protected $aliasCleaner;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * API Base URL.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * SegnalaMeController constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   HTTP Client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   * @param \Drupal\pathauto\AliasCleanerInterface $aliasCleaner
   *   Alias Cleaner.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   Configurations.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File System.
   */
  public function __construct(ClientInterface $client, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, AliasCleanerInterface $aliasCleaner, ImmutableConfig $config, FileSystemInterface $file_system = NULL) {
    $this->httpClient = $client;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->aliasCleaner = $aliasCleaner;
    $this->baseUrl = $config->get('wso2_with_jwt.base_url') . '/segnalame/api/public';
    $this->fileSystem = $file_system;
  }

  /**
   * Controller lazy building.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   *
   * @return \Drupal\segnalame\Controller\SegnalaMeController|void
   *   Retrun value.
   */
  public static function create(ContainerInterface $container) {
    /** @var \GuzzleHttp\Client $httpClient */
    $httpClient = $container->get('http_client');
    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = $container->get('messenger');
    /** @var \Drupal\pathauto\AliasCleaner $aliasCleaner */
    $aliasCleaner = $container->get('pathauto.alias_cleaner');
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $container->get('config.factory')->get('wso2_with_jwt.settings');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = $container->get('file_system');
    return new static($httpClient, $entityTypeManager, $messenger, $aliasCleaner, $config, $file_system);
  }

  /**
   * CRUD operations to executes on segnalame services.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $instituteId
   *   Institute id.
   * @param string $operationType
   *   Operation type.
   */
  public function crud(Request $request, int $instituteId, string $operationType) {
    // Get x-pdm-token.
    $token = $request->headers->get('x-pdm-token');

    if ($token === self::TOKEN) {
      switch ($operationType) {
        case 'create':
        case 'update':
          return $this->createSectors($instituteId);

        case 'delete':
          return $this->deleteSectors($instituteId);
      }
    }
    else {
      return new JsonResponse(['401' => 'Unauthorized request.']);
    }
  }

  /**
   * Get institutes.
   *
   * @param int|null $id
   *   Id.
   *
   * @return false|mixed|string
   *   Institute(s) array
   */
  public function getInstitutes(int $id = NULL) {
    try {
      // Return all institutes.
      $institutes = $this->httpClient->get($this->baseUrl . '/institutes');
      $institutes = json_decode($institutes->getBody()->getContents());
      if ($id === NULL) {
        return $institutes;
      }
      else {
        foreach ($institutes as $institute) {
          if ($institute->id === $id) {
            return $institute;
          }
        }

        return FALSE;
      }
    }
    catch (ClientException $exception) {
      return $exception->getMessage();
    }
  }

  /**
   * Get sectors.
   *
   * @param int $instituteId
   *   Insitute id.
   *
   * @return mixed
   *   Sectors.
   */
  private function getSectors(int $instituteId) {
    // Get sectors from backoffice API.
    $sectors = $this->httpClient->get($this->baseUrl . '/institutes/' . $instituteId . '/sectors');
    $sectors = $sectors->getBody()->getContents();
    // Decode json string.
    $sectors = json_decode($sectors);
    return $sectors;
  }

  /**
   * Create or update sectors relative to given institute ID.
   *
   * @param int $instituteId
   *   Institute id.
   */
  private function createSectors($instituteId = 1251) {
    $contentType = 'settore_segnala_me';
    $institute = $this->getInstitutes($instituteId);

    try {
      $sectors = $this->getSectors($instituteId);

      // Get entity storage.
      $entityStorage = $this->entityTypeManager->getStorage('node');

      foreach ($sectors as $sector) {
        // Check if doesn't already exists the sector with given id.
        $entity = $entityStorage->loadByProperties(['field_backoffice_id' => $sector->id]);
        if (empty($entity)) {
          // Create the new node based on sectors' values.
          $sectorNode = Node::create(['type' => $contentType]);
        }
        else {
          $sectorNode = reset($entity);
        }

        $comune = $entityStorage->loadByProperties([
          'type'  => 'comune_servizi',
          'title' => ucwords($institute->name),
        ]);

        // Get comune.
        $comune = count($comune) > 0 ? reset($comune) : NULL;

        // Service's URI and Title.
        $comuneLower = strtolower($comune->label());
        $baseHost    = Url::fromUserInput('/', ['absolute' => TRUE])->toString();
        $sectorLink  = [
          'uri'     => "$baseHost/servizi/$comuneLower/segnala-me/segnala/$institute->id/$sector->id",
          'title'   => 'Segnala',
          'options' => [
            'absolute' => TRUE,
          ],
        ];

        if ($sector->imagePath !== NULL && !empty($sector->imagePath)) {
          $imagePath = str_replace('api/public/', '', $sector->imagePath);
          // Create D8 File entity.
          $fileData = file_get_contents($this->baseUrl . $imagePath);
          $directory = 'public://sectors/';
          $exists = $this->fileSystem->prepareDirectory($directory, 1);
          if ($exists) {
            $imageFile = file_save_data($fileData, $directory . date('Ymd-Hisv') . '.jpg');
            $imageFile = $imageFile->id();
          }

        }
        else {
          $imageFile = NULL;
        }

        // Reset institutes values.
        $sectorNode
          ->set('title', $sector->name)
          ->set('body', $sector->description)
          ->set('field_cover_image', $imageFile)
          ->set('field_abstract', $sector->subtitle)
          ->set('field_backoffice_id', $sector->id)
          ->set('field_comune_servizi', $comune)
          ->set('field_url_servizio', $sectorLink);

        // Save the created sector on db.
        $sectorNode->save();
      }

    }
    catch (ClientException $exception) {
      return $exception->getMessage();
    }

    return new JsonResponse([$sectors]);
  }

  /**
   * Delete sectors that not exists on backoffice.
   *
   * @param int $instituteId
   *   Institute id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  private function deleteSectors(int $instituteId) {
    $removed = [];
    $sectors = $this->getSectors($instituteId);

    // Remap sectors.
    $sectors = array_map(function ($sector) {
      return $sector->id;
    }, $sectors);

    $entityStorage = $this->entityTypeManager->getStorage('node');
    // Get all entities with field_backoffice_id setted.
    $ids = $entityStorage->getQuery()
      ->exists('field_backoffice_id')
      ->execute();

    // Check the sectors that only exists in drupal and not in the backoffice.
    foreach ($ids as $id) {
      $entity = $entityStorage->load($id);
      if (!in_array($entity->field_backoffice_id->value, $sectors)) {
        $removed[] = $entity->id();

        // Delete.
        $entity->delete();
      }
    }

    return new JsonResponse(['removed_id' => $removed]);
  }

}
