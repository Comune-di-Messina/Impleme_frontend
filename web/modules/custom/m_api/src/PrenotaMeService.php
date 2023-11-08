<?php

namespace Drupal\m_api;

use Drupal\wso2_with_jwt\Exceptions\UnableToAuthorizeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\wso2_with_jwt\AuthType;

/**
 * Class PrenotaMeService.
 */
class PrenotaMeService extends MClientService {

  /**
   * Retrieve stati prenotazione form cache or from API.
   */
  public function getStatiPrenotazione() {
    if ($stati = $this->stateService->get('m_api.prenotame.stati')) {
      return $stati;
    }

    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      $result = $this->wso2FactoryService->get('newbolite/v2/roombookings/status', $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }

    $this->logger->info('Refresh dati stati prenotazioni.');
    if (is_a($result, 'Exception')) {
      $this->logger->error('Errore nel caricamento degli stati della prenotazione.');

      return [];
    }
    $this->stateService->set('m_api.prenotame.stati', $result);

    return $result;
  }

  /**
   * CALL API PrenotaME bookings.
   */
  public function getPrenotazioni(?string $cf = NULL) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    if ($cf === NULL) {
      $cf = $this->getCurrentCf();
    };
    try {
      return $this->wso2FactoryService->get("newbolite/v2/roombookings/bookings/users/$cf", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME booking.
   */
  public function getPrenotazione(string $id) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      return $this->wso2FactoryService->get("newbolite/v2/roombookings/bookings/$id", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME annulla.
   */
  public function annullaPrenotazione(string $id) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      return $this->wso2FactoryService->delete("newbolite/v2/messages/casefiles/$id", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME documents.
   */
  public function getDocumentMetaData(string $id) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      return $this->wso2FactoryService->get("newbolite/v2/documents/$id", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME documents.
   */
  public function getDocumentContent(string $id) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      return $this->wso2FactoryService->get("newbolite/v2/documents/$id/content", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME dettagli sala.
   */
  public function getDettagliSala(string $id) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      return $this->wso2FactoryService->get("newbolite/v2/public/rooms/$id", $options, AuthType::NONE);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * Retrieve enti form cache or from API.
   */
  public function getTipiStrutture() {
    $stateKey = 'm_api.rooms.tipoStrutture';
    $options = $this->defaultHttpOptionsWithQuery([]);
    try {
      $result = $this->wso2FactoryService->get('newbolite/v2/public/rooms/typologies', $options, AuthType::NONE);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }

    $this->logger->info('Refresh dati tipologie strutture.');
    if (is_a($result, 'Exception')) {
      $this->logger->error('Errore nel caricamento deglle tipologie strutture.');

      return [];
    }
    $strutture = [];
    foreach ($result as $key => $value) {
      $strutture[$value->id] = $value->tipo;
    }
    $this->stateService->set($stateKey, $strutture);

    return $strutture;
  }

  /**
   * CALL API PrenotaME recupera disponibilita.
   */
  public function recuperaDisponibilita(string $id, object $data) {
    $query = [
      'dayEnd'   => $data->giornoA === $data->giornoDa ? NULL : $data->giornoA,
      'dayStart' => $data->giornoDa,
      'smart'    => $data->giornoA !== $data->giornoDa,
      'tariffa'    => $data->tariffa,
    ];
    if ($data->giornoA === $data->giornoDa) {
      unset($query['dayEnd']);
    }
    $options = $this->defaultHttpOptionsWithQuery($query);
    try {
      return $this->wso2FactoryService->get("newbolite/v2/public/rooms/" . $id . "/availabilitis", $options, AuthType::NONE);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME verifica prezzo.
   */
  public function verificaPrezzo(string $id, object $data) {
    $query = [
      'tariffa' => $data->tariffa,
      'dayEnd'         => $data->giornoA,
      'dayStart'       => $data->giornoDa,
      'eventId'        => $data->tipoEvento,
      'hourEnd'        => $data->oraA,
      'hourStart'      => $data->oraDa,
      'roomId'         => $id,
      'services'       => implode(',', array_map(function ($value) {
        return $value->id;
      }, $data->serviziPrenotati)),
      'interaGiornata' => property_exists($data, 'interaGiornata') ? $data->interaGiornata : FALSE,
    ];
    if ($data->interaGiornata) {
      unset($query['hourStart']);
      unset($query['hourEnd']);
      unset($query['dayEnd']);
    }
    if (empty($query['services'])) {
      unset($query['services']);
    }
    $options = $this->defaultHttpOptionsWithQuery($query);
    try {
      $test = $this->wso2FactoryService->get("newbolite/v2/roombookings/bookings/prices", $options, AuthType::BOTH);
      return $test;
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }

  }

  /**
   * CALL API PrenotaME prenota sala.
   */
  public function prenotaSala($id, $data) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    // $this->addJwt($options);
    $options['body']                    = $data;
    $options['headers']['Content-Type'] = 'application/json';
    try {
      return $this->wso2FactoryService->post("newbolite/v2/roombookings", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

  /**
   * CALL API PrenotaME prenota sala.
   */
  public function aggiungiAllegati($id, $data) {
    $options = $this->defaultHttpOptionsWithQuery([]);
    $options['body'] = $data;
    $options['headers']['Content-Type'] = 'application/json';
    try {
      return $this->wso2FactoryService->post("newbolite/v2/casefiles/$id/documents", $options, AuthType::BOTH);
    }
    catch (UnableToAuthorizeException $e) {
      $variables = [
        '@message' => 'Unable to authorize request',
        '@error_message' => $e->getMessage(),
      ];
      $this->logger->error('@message. Details: @error_message', $variables);

      if ($this->session_manager->isStarted()) {
        $this->session_manager->destroy();
      }
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

}
