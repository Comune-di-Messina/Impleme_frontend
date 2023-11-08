<?php

namespace Drupal\m_api;

use CURLFile;
use Drupal\wso2_with_jwt\AuthType;
use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use Exception;

/**
 * Class SegnalaMeService.
 */
class SegnalaMeService extends MClientService {

  /**
   * CALL API SegnalaME SottoAree.
   *
   * @param string $instituteId
   *   Institute id.
   * @param string $sectorId
   *   Sector id.
   *
   * @return array
   *   Return value.
   */
  public function recuperaSottoAree(string $instituteId, string $sectorId) {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("segnalame/api/institutes/$instituteId/sectors/$sectorId/sub-sectors", $options, AuthType::BOTH);
      $this->logger->info('recuperaSottoAree');
      $sottoAree = [];
      foreach ($result as $key => $value) {
        $sottoAree[] = [
          'value' => $value->id,
          'text'  => $value->name,
        ];
      }

      usort($sottoAree, static function ($item1, $item2) {
        return $item1['text'] <=> $item2['text'];
      });

      return $sottoAree;
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Aree.
   *
   * @param string $instituteId
   *   Institute id.
   *
   * @return array
   *   Return value.
   */
  public function recuperaAree(string $instituteId) {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("/segnalame/api/institutes/$instituteId/sectors?sort=name,asc", $options, AuthType::BOTH);
      $sottoAree = [];
      foreach ($result as $key => $value) {
        $sottoAree[] = [
          'value' => $value->id,
          'text'  => $value->name,
        ];
      }

      return $sottoAree;
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Stati.
   */
  public function recuperaStati() {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("/segnalame/api/reportings/statuses?sort=value,asc", $options, AuthType::BOTH);

      $stati = [];
      foreach ($result as $key => $value) {
        $stati[] = [
          'value' => $value->id,
          'text'  => $value->id === 6 ? 'Chiusa' : $value->value,
        ];
      }

      usort($stati, static function ($item1, $item2) {
        return $item1->text <=> $item2->text;
      });

      return $stati;
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Segnalazioni.
   */
  public function recuperaSegnalazioni($page, $body) {
    $size    = 6;
    $sort    = '&sort=insertTs,desc';
    $data    = json_decode($body, TRUE);
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();

    try {
      $token = $this->wso2FactoryService->getFreshAccessToken();
      $jwt   = $this->wso2FactoryService->getJWT();
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }

    $curl     = curl_init();
    $statusID = $data['statusID'] ? '&statusId.equals=' . $data['statusID'] : '';
    $sectorID = $data['sectorID'] ? '&sectorId.equals=' . $data['sectorID'] : '';
    $filters  = $statusID . $sectorID;
    $headers  = [];

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . '/segnalame/api/reportings/search?page=' . $page . '&size=' . $size . $filters . $sort,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => 'GET',
      CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $token",
        "X-Auth-Token: $jwt",
      ],
      CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$headers) {
        $matches = [];
        if (preg_match('/^([^:]+)\s*:\s*([^\x0D\x0A]*)\x0D?\x0A?$/', $header, $matches)) {
          $headers[$matches[1]][] = $matches[2];
        }

        return strlen($header);
      },
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return [
      'items'       => json_decode($response, TRUE),
      'totalCount'  => (int) $headers['X-Total-Count'][0],
      'pages'       => (int) ceil($headers['X-Total-Count'][0] / $size),
      'currentPage' => (int) $page,
      'size'        => $size,
    ];
  }

  /**
   * CALL API SegnalaME Segnalazione.
   */
  public function dettaglioSegnalazione($id) {
    try {
      $baseUrl = $this->client->getConfig()["base_uri"]->getHost();
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("/segnalame/api/reportings/$id", $options, AuthType::BOTH);

      $images    = [];
      $documents = [];
      foreach ($result->files as $key => $file) {
        $data   = base64_encode($this->getFileContent($id, $file->uuid));
        $base64 = "data:$file->type;base64,$data";
        if ($file->type !== 'application/pdf') {
          $images[] = [
            'base64' => $base64,
            'name'   => $file->name,
          ];
        }
        else {
          $documents[] = [
            'base64' => $base64,
            'name'   => $file->name,
          ];
        }
      }

      $result->images    = $images;
      $result->documents = $documents;

      // curl_close($curl);
      return $result;
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Recupera Contenuto Files.
   */
  public function getFileContent($id, $uuid) {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $result = $this->wso2FactoryService->get("/segnalame/api/reportings/$id/files/$uuid/content", $options, AuthType::BOTH);

      return $result;
      // Return $response;.
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Aggiungi Segnalazione.
   */
  public function aggiungiSegnalazione($id, $data, $instituteId, $sectorId) {
    try {
      $options = $this->defaultHttpOptionsWithQuery([]);
      $options['body'] = $data;
      $result = $this->wso2FactoryService->post(
        "/segnalame/api/institutes/$instituteId/sectors/$sectorId/sub-sectors/$id/newReporting",
        $options,
        AuthType::BOTH
      );
      return $result;
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * CALL API SegnalaME Upload File.
   */
  public function uploadFile($data) {
    $baseUrl = $this->client->getConfig();

    try {
      $token = $this->wso2FactoryService->getFreshAccessToken();
      $jwt   = $this->wso2FactoryService->getJWT();
    }
    catch (UnableToAuthorizeException $e) {
      $this->logErrorAndLogout('Unable to authorize request', $e->getMessage());
    }
    catch (Exception $e) {
      throw $e;
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl["base_uri"]->getHost() . '/segnalame/api/reportings/files/upload',
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => 'POST',
      CURLOPT_POSTFIELDS     => [
        'file' => new CURLFILE(
          $data['file']->getPathName(),
          $data['file']->getClientMimeType(),
          $data['file']->getClientOriginalName()
        ),
      ],
      CURLOPT_HTTPHEADER     => [
        'Content-Type: multipart/form-data',
        "Authorization: Bearer $token",
        "X-Auth-Token: $jwt",
      ],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return json_decode($response, TRUE);
  }

  /**
   * CALL API SegnalaME geoDecode.
   */
  public function geoDecode($coords) {
    // $apiKey = 'AIzaSyDXjOCDBEIWap1f5f3w0MPN7LAntBSvYSw';
    $url  = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $coords . '&key=' . $this->gmapsKey . '&language=it';
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => 'GET',
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    return json_decode($response, TRUE)['results'][0]['formatted_address'];
  }

}
