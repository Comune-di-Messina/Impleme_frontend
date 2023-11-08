<?php

namespace Drupal\m_api;

use Drupal\Core\Url;
use Drupal\wso2_with_jwt\AuthType;
use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use Exception;

/**
 * Class PrenotaUfficioService.
 */
class PrenotaUfficioService extends MClientService {

  // Messina code.
  const DEFAULT_MUNICIPALITY_CODE = 'SIF07';

  /**
   * CALL API PrenotaUfficio Servizi ufficio.
   *
   * @param string $serviceType
   *   Description.
   * @param string|null $officeId
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getPublicServices(string $serviceType, string $officeId = NULL) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();
    $curl    = curl_init();
    $params  = $serviceType ? '?serviceTypeId=' . $serviceType : '';
    $params .= $officeId ? '&officeId=' . $officeId : '';

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . '/prenota-ufficio/v1/publicservice' . $params,
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

    return json_decode($response, TRUE);
  }

  /**
   * CALL API PrenotaUfficio Lista Uffici.
   *
   * @param string $municipalityId
   *   Description.
   * @param string|null $serviceType
   *   Description.
   * @param string|null $serviceId
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getOfficesList(string $municipalityId, string $serviceType = NULL, string $serviceId = NULL) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();
    $curl    = curl_init();
    $params  = $serviceType ? '?serviceType=' . $serviceType : '';
    $params .= $serviceId ? '&serviceId=' . $serviceId : '';

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . '/prenota-ufficio/v1/municipality/' . $municipalityId . '/office' . $params,
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

    return array_map(static function ($item) use ($municipalityId) {
      $item['link'] = Url::fromRoute('m_api.prenota_ufficio.prenota', [
        'municipalityName' => _m_core_get_municipality_name_from_code($municipalityId),
        'officeId'       => $item['id'],
      ])->toString();

      return $item;
    }, json_decode($response, TRUE));
  }

  /**
   * CALL API PrenotaUfficio serviceTypes.
   *
   * @param string $municipalityId
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getServiceTypes(string $municipalityId = NULL) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();
    $curl    = curl_init();
    $params  = $municipalityId ? '?municipalityId=' . $municipalityId : '';

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . '/prenota-ufficio/v1/serviceTypes' . $params,
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

    return json_decode($response, TRUE);
  }

  /**
   * CALL API PrenotaUfficio Dettagli Ufficio.
   *
   * @param string $municipalityId
   *   Description.
   * @param string $officeId
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getOfficeDetails(string $municipalityId, string $officeId) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();
    $curl    = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . "/prenota-ufficio/v1/municipality/$municipalityId/office/$officeId",
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

    return json_decode($response, TRUE);
  }

  /**
   * CALL API PrenotaUfficio Calendario.
   *
   * @param object $params
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getOfficeCalendar(object $params) {
    $baseUrl        = $this->client->getConfig()["base_uri"]->getHost();
    $publicService  = $params->get('publicServiceId');
    $officeId       = $params->get('officeId');
    $municipalityId = $params->get('municipalityId');
    $startDate      = $params->get('startDate');
    $endDate        = $params->get('endDate');
    $curl           = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL            => $baseUrl . "/prenota-ufficio/v1/calendar?publicServiceId=$publicService&officeId=$officeId&municipalityId=$municipalityId&startDate=$startDate&endDate=$endDate",
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => 'GET',
    ]);

    $response = curl_exec($curl);

    return json_decode($response, TRUE);
  }

  /**
   * CALL API PrenotaUfficio Prenotazioni.
   *
   * @param string $municipalityId
   *   Description.
   * @param string $userCF
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function getReservationsList(string $municipalityId, string $userCF) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();

    try {
      // Wso2Connection::getAccessToken();
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
      CURLOPT_URL            => $baseUrl . "/prenota-ufficio/v1/reservation?userId=$userCF&municipalityId$municipalityId",
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
    ]);

    $response = curl_exec($curl);

    return json_decode($response, TRUE);
  }

  /**
   * CALL API PrenotaUfficio Aggiungi prenotazione.
   *
   * @param string $body
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function addReservation(string $body) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();

    $options = $this->defaultHttpOptionsWithQuery([]);
    $options['json'] = json_decode($body);
    $response = $this->wso2FactoryService->post('/prenota-ufficio/v1/reservation', $options, AuthType::BOTH);
    $response->qrcodeUrl = $baseUrl . '/prenota-ufficio/v1/reservation/' . $response->id . '/qrcode';

    return $response;
  }

  /**
   * CALL API PrenotaUfficio Cancella prenotazione.
   *
   * @param string $requestId
   *   Description.
   * @param string $body
   *   Description.
   *
   * @return array
   *   Return value.
   */
  public function clearReservation(string $requestId, string $body) {
    $baseUrl = $this->client->getConfig()["base_uri"]->getHost();

    $options = $this->defaultHttpOptionsWithQuery([]);
    $options['json'] = json_decode($body);
    $response = $this->wso2FactoryService->patch("/prenota-ufficio/v1/reservation/$requestId", $options, AuthType::BOTH);

    return $response;
  }

}
