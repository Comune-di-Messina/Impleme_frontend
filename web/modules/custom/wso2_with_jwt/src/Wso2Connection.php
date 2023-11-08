<?php

namespace Drupal\wso2_with_jwt;

use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Wso2Connection.
 *
 * @package Drupal\iguana
 */
class Wso2Connection {

  /**
   * Connection settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config = NULL;

  /**
   * Wso2Connection constructor.
   */
  public function __construct() {
    $this->config = \Drupal::config('wso2_with_jwt.settings');
  }

  /**
   * Get configuration or state setting for this Iguana integration module.
   *
   * @param string $name
   *   This module's config or state.
   *
   * @return mixed
   *   The configuration value.
   */
  protected function getConfig($name) {
    return $this->config->get('wso2_with_jwt.' . $name);
  }

  public static function getAccessToken() {
    $accessToken = NULL;
    $tempstore = \Drupal::service('user.private_tempstore')->get('wso2_with_jwt');
    if ($tempstore->get('WSO2_TOKEN_M2M')) {
      $wso2m2mToken = $tempstore->get('WSO2_TOKEN_M2M');
      if (!is_object($wso2m2mToken))
        $wso2m2mToken = (object) $wso2m2mToken;

      if (is_object($wso2m2mToken) && property_exists($wso2m2mToken, 'access_token')) {
        $accessToken = $wso2m2mToken->access_token;
      }
    }
    return $accessToken;
  }

  public static function getRefreshToken() {
    $refresh = NULL;
    $tempstore = \Drupal::service('user.private_tempstore')->get('wso2_with_jwt');
    if ($tempstore) {
      $wso2m2mToken = $tempstore->get('WSO2_TOKEN_M2M');
      $refresh = $wso2m2mToken->refresh_token;
      $tempstore->set('WSO2_JWT', $wso2m2mToken->id_token);
    }
    return $refresh;
  }

  public static function getJWT(){
    $tempstore = \Drupal::service('user.private_tempstore')->get('wso2_with_jwt');
    return $tempstore->get('WSO2_JWT');
  }

  public static function saveToken($tokens){
    $tempstore = \Drupal::service('user.private_tempstore')->get('wso2_with_jwt');
    $tempstore->set('WSO2_TOKEN_M2M', $tokens);
    if(property_exists($tokens, 'id_token')){
      $tempstore->set('WSO2_JWT', $tokens->id_token);
    }
  }

  /**
   * Ad ogni metodo pubblico esegue la lofica di recupero access token o rinnovo token
   */
  public function __call($name, $args) {
    if (method_exists($this, $name)) {
      if (NULL != self::getAccessToken()) {
        $this->retriveAccessoToken();
      }
      try {
        return call_user_func_array([$this, $name], $args);
      }
      catch (ClientException $e) {
        switch ($e->getCode()) {
          case 400:
          case 401:
            $this->retriveAccessoToken();
            return call_user_func_array([$this, $name], $args);

          break;
          default:
            return $e;
        }
      }
    }
  }

  /**
   * Recupera l'access token da WSO2.
   */
  protected function retriveAccessoToken() {
    $baseUrl = $this->getConfig('base_url');
    $client_id = $this->getConfig('client_id');
    $client_secret = $this->getConfig('client_secret');


    $client = new Client([
      'verify' => FALSE,
      'base_uri' => $baseUrl,
    ]);

    $response = $client->post('token', [
        'headers' => [
          'Authorization'  => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
          'Content-Type'   => 'application/x-www-form-urlencoded',
        ],
        'proxy' => [
          // Don't use a proxy with these.
          'no' => [$baseUrl]
        ],
        'form_params' => [
          'grant_type' => 'client_credentials',
        ]
    ]);

    $tokens = json_decode($response->getBody()->getContents());

    self::saveToken($tokens);
  }

  /**
   * Renew Access Token
   */
  protected function renewAccessToken(){
      // curl -k -d "grant_type=refresh_token&refresh_token=<retoken>" -H "Authorization: Basic SVpzSWk2SERiQjVlOFZLZFpBblVpX2ZaM2Y4YTpHbTBiSjZvV1Y4ZkM1T1FMTGxDNmpzbEFDVzhh" -H "Content-Type: application/x-www-form-urlencoded" https://localhost:8243/token
      $baseUrl = $this->getConfig('base_url');
      $client_id = $this->getConfig('client_id');
      $client_secret = $this->getConfig('client_secret');

      $client = new Client([
        'verify' => false,
        'base_uri' => $baseUrl,
      ]);

      $response = $client->post('token', [
          'headers' => [
            'Authorization'  => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
            'Content-Type'   => 'application/x-www-form-urlencoded',
          ],
          'proxy' => [
            'no' => [$baseUrl]    // Don't use a proxy with these
          ],
          'form_params' => [
              'grant_type' => 'refresh_token',
              'refresh_token' => self::getRefreshToken(),
          ]
      ]);

      $tokens = json_decode($response->getBody()->getContents());
      self::saveToken((object) $tokens);
    }
}
