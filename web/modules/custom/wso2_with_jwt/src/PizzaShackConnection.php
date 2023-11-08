<?php
 
namespace Drupal\wso2_with_jwt;
 
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\BadResponseException;
 
 
/**
 * Class Wso2Connection
 *
 * @package Drupal\iguana
 */
class PizzaShackConnection extends Wso2Connection {
 
   /**
   * CALL API Salvataggio le preference utente
   */
  protected function getMenu() {
    $baseUrl = $this->getConfig('base_url');
    $client = new Client([
      'verify' => false,
      'base_uri' => $baseUrl, 
    ]);

    $response = $client->get('pizzashack/1.0.0/menu', [
        'headers' => [
          'Authorization'  => 'Bearer ' . self::getAccessToken(),
          'Content-Type'   => 'application/json',
          'JWT'            => self::getJWT(),
        ],
    ]);

    return json_decode($response->getBody()->getContents());
  }
}