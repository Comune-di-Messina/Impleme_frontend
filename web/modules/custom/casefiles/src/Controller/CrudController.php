<?php

namespace Drupal\casefiles\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\casefiles\CasefilesService;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * SegnalaMe Controller.
 */
class CrudController extends ControllerBase {
  const CATEGORY = 'Pratiche';
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
   * Casefile service.
   *
   * @var \Drupal\casefiles\CasefilesService
   */
  protected $casefilesServices;

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
   * CasefilesController constructor.
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
   *   Casefiles Service.
   * @param \Drupal\casefiles\CasefilesService $casefilesService
   *   Configurations.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File System.
   */
  public function __construct(ClientInterface $client, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger, AliasCleanerInterface $aliasCleaner, ImmutableConfig $config, CasefilesService $casefilesService, FileSystemInterface $fileSystem = NULL) {
    $this->httpClient = $client;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->aliasCleaner = $aliasCleaner;
    $this->baseUrl = $config->get('wso2_with_jwt.base_url') . '/casefiles/api/public';
    $this->casefilesService = $casefilesService;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Controller lazy building.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   *
   * @return CaseFilesController|void
   *   Retrun value.
   */
  public static function create(ContainerInterface $container) {
    /**
* @var \GuzzleHttp\Client $httpClient
*/
    $httpClient = $container->get('http_client');

    /**
* @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
*/
    $entityTypeManager = $container->get('entity_type.manager');

    /**
* @var \Drupal\Core\Messenger\Messenger $messenger
*/
    $messenger = $container->get('messenger');

    /**
* @var \Drupal\pathauto\AliasCleaner $aliasCleaner
*/
    $aliasCleaner = $container->get('pathauto.alias_cleaner');

    /**
* @var \Drupal\Core\Config\ImmutableConfig $config
*/
    $config = $container->get('config.factory')->get('wso2_with_jwt.settings');

    /**
* @var \Drupal\Core\File\FileSystemInterface $fileSystem
*/
    $fileSystem = $container->get('file_system');

    /**
* @var \Drupal\casefiles\CasefilesService $casefileService
*/
    $casefilesService = $container->get('casefiles.casefiles_client');

    return new static($httpClient, $entityTypeManager, $messenger, $aliasCleaner, $config, $casefilesService, $fileSystem);
  }

  /**
   * CRUD operations to executes on segnalame services.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $typeId
   *   Institute id.
   * @param string $operationType
   *   Operation type.
   */
  public function crud(Request $request, int $typeId, string $operationType) {
    // Get x-pdm-token.
    $token = $request->headers->get('x-pdm-token');

    if ($token === self::TOKEN) {
      switch ($operationType) {
        case 'create':
        case 'update':
          return $this->createType($typeId, $operationType);

        case 'delete':
          return $this->deleteType($typeId);
      }
    }
    else {
      return new JsonResponse(['401' => 'Unauthorized request.']);
    }
  }

  /**
   * Create or update sectors relative to given institute ID.
   *
   * @param object $typeId
   *   Type id.
   * @param string $operationType
   *   Operation Type.
   *
   * @example [{
   *    "codice": "P03",
   *    "nome": "Passi Carrabili",
   *    "descrizione": "pratica passi carrabili",
   *    "note": "Note",
   *    "libero": "Testo Libero",
   *    "id": "2"
   * }]
   */
  private function createType($typeId, $operationType) {
    $node = NULL;
    $type = $this->casefilesService->getTipologiaPratica($typeId);
    $nodeType = 'settore_pratiche_tipologia';

    try {
      if (!empty($type)) {
        // Get entity storage.
        $entityStorage = $this->entityTypeManager->getStorage('node');
        // Check if doesn't already exists the type with given name.
        $nodes = $entityStorage->loadByProperties(['field_backoffice_id' => $type->id, 'type' => $nodeType]);

        if ((empty($nodes) && $operationType === 'create') || (!empty($nodes) && $operationType === 'update')) {
          if ($operationType === 'create') {
            $node = Node::create(['type' => $nodeType]);
          }
          else {
            $node = reset($nodes);
          }

          $baseHost = Url::fromUserInput('/', ['absolute' => TRUE])->toString();

          if (isset($type->img)) {
            $decImg = base64_decode($type->img);
            $decImgMime = mime_content_type($decImg);
            $directory  = 'public://types';
            \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
            $fileImg = file_save_data($decImg, 'public://types/' . $type->id . '.jpg');
            $fileImg = $fileImg->id();
          }
          else {
            $fileImg = NULL;
          }
          // Create / update the node.
          $node = $this->createNode($node, $nodeType, $type, $typeId, $baseHost, $fileImg);
        }
        else {
          $node = reset($nodes);
        }
      }
    }
    catch (ClientException $exception) {
      return $exception->getMessage();
    }
    catch (EntityStorageException $e) {
      return $e->getMessage();
    }

    if ($node === NULL || $node === FALSE) {
      $node = Node::create(['type' => $nodeType]);
      $node = $this->createNode($node, $nodeType, $type, $typeId, $baseHost, $fileImg);
    }

    $response = ['node_id' => $node->id(), 'name' => $node->label()];

    return new JsonResponse($response);
  }

  /**
   * Delete sectors that not exists on backoffice.
   *
   * @param int $typeId
   *   Institute id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  private function deleteType(int $typeId) {
    $toRemove = $this->entityTypeManager->getStorage('node')->loadByProperties(['field_backoffice_id' => $typeId]);

    if (!empty($toRemove)) {
      $toRemove = reset($toRemove);
      $toRemove->delete();
    }

    return new JsonResponse(['removed_id' => $toRemove->id()]);
  }

  /**
   * @return EntityBase
   */
  private function createNode(EntityBase $node, $nodeType, $type, $typeId, $baseHost, $fileImg): EntityBase {
    $node
      ->set('type', $nodeType)
      ->set('title', $type->nome)
      ->set('field_name', $type->nome)
      ->set('body', $type->descrizione)
      ->set('field_backoffice_id', $type->id)
      ->set('field_code', $type->codice)
      ->set('field_note', $type->note)
      ->set('field_free', $type->libero)
      ->set('field_url_servizio', [
        'uri' => $baseHost . '/servizi/pratiche/nuova?type=' . $typeId,
        'title' => 'Crea una pratica per ' . $type->nome,
      ])
      ->set('field_cover_image', $fileImg)
      ->setOwnerId(1)
      ->save();

    return $node;
  }

}
