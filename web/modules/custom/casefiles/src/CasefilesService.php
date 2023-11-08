<?php

namespace Drupal\casefiles;

use Drupal\m_api\MClientService;
use Drupal\wso2_with_jwt\AuthType;
use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * CasefilesService class.
 */
class CasefilesService extends MClientService {
  const API_ENDPOINTS = [
    'getPratica'  => 'newbolite/v2/casefiles/:id',
    'postPratica' => 'newbolite/v2/casefiles/manuale',
    'getListaPratiche'    => 'newbolite/v2/casefiles/users/:cf',
    'getAllegatoMetadati' => 'newbolite/v2/documents/:id',
    'getAllegatoContent'  => 'newbolite/v2/documents/:id/content',
    'postAllegato'        => 'newbolite/v2/casefiles/:id/documents',
    'getTipologiePratica' => 'newbolite/v2/public/types',
  ];

  /**
   * Restituisce la lista delle pratiche dato il codice fiscale.
   *
   * @param mixed $end
   *   End date.
   * @param mixed $enteId
   *   ID ente.
   * @param mixed $start
   *   Start date.
   * @param mixed $stato
   *   Casefile State.
   *
   * @return \GuzzleHttp\Psr7\Response|mixed|void|null
   *   Return.
   */
  public function getListaPratiche($end = NULL, $enteId = NULL, $start = NULL, $stato = NULL) {
    $options = [
      'end'    => $end,
      'enteId' => $enteId,
      'start'  => $start,
      'stato'   => $stato,
    ];
    $endpoint = str_replace(':cf', $this->getCurrentCf(), self::API_ENDPOINTS['getListaPratiche']);
    return $this->request($endpoint, $options);
  }

  /**
   * Get casefile.
   *
   * @param string $id
   *   Id casefile.
   *
   * @return \GuzzleHttp\Psr7\Response|mixed|void|null
   *   Return.
   */
  public function getPratica(string $id) {
    $endpoint = str_replace(':id', $id, self::API_ENDPOINTS['getPratica']);

    return $this->request($endpoint, []);
  }

  /**
   * Post casefile.
   */
  public function postPratica(Request $request) {
    $casefile = $request->getContent('');
    $casefile = json_decode($casefile);

    try {
      return $this->request(self::API_ENDPOINTS['postPratica'], ['json' => $casefile], 'post');
    }
    catch (ClientException $exception) {
      throw $exception;
    }
  }

  /**
   * Allegato.
   *
   * @param string $id
   *   Id.
   *
   * @return \Laminas\Diactoros\Response\JsonResponse
   *   Response.
   */
  public function getAllegato(string $id) {
    // Metadati.
    $endpoint = str_replace(':id', $id, self::API_ENDPOINTS['getAllegatoMetadati']);
    $metadati = $this->request($endpoint, []);

    // Content.
    $endpoint .= '/content';
    $content = $this->request($endpoint, []);
    $content = base64_encode($content);
    $response = new JsonResponse(['metadati' => $metadati, 'content' => $content]);
    return $response;
  }

  /**
   * Aggiunge allegato alla pratica.
   *
   * @param Request $request
   *   Request.
   * @param string $casefileId
   *   Casefile Id.
   *
   * @return JsonResponse
   *   Response.
   */
  public function postAllegato(Request $request, string $casefileId): JsonResponse {
    $body = $request->getContent('');
    $body = json_decode($body);

    $endpoint = str_replace(':id', $casefileId, self::API_ENDPOINTS['postAllegato']);

    $response = $this->request($endpoint, ['json' => $body], 'post');

    return new JsonResponse($response, 200);
  }

  /**
   * Returns the given casefile type.
   *
   * @param string $typeId
   *   Type id.
   *
   * @return array|null
   *   Return.
   */
  public function getTipologiaPratica($typeId) {
    $types = $this->getTipologiePratica();
    foreach ($types as $type) {
      if ($type->id == $typeId) {
        return $type;
      }
    }
    return NULL;
  }

  /**
   * Restituisce le tipologie di pratica.
   *
   * @return \GuzzleHttp\Psr7\Response|mixed|void|null
   *   Return.
   */
  public function getTipologiePratica() {
    return $this->request(self::API_ENDPOINTS['getTipologiePratica'], [], 'get', AuthType::NONE);
  }

  /**
   * @return JsonResponse
   */
  public function listaEnti() {
    return $this->request('https://gw-dev.impleme.giottolabs.com/pagopa-tributi/v2/enti', []);
  }

  /**
   * {@inheritdoc}
   */
  private function request($endpoint, $options, $method = 'get', $authType = AuthType::BOTH) {
    try {
      return $this->wso2FactoryService->$method($endpoint, $options, $authType);
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
      return new JsonResponse(
        [
          'error' => $e->getMessage(),
          'code' => $e->getCode(),
        ]
      );
    }
    catch (ClientException $e) {
      return new JsonResponse(
        [
          'error' => $e->getMessage(),
          'code' => $e->getCode(),
        ]
      );
    }
  }

}
