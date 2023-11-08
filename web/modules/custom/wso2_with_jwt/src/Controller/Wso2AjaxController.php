<?php

/**
 * @file
 * Contains \Drupal\wso2_with_jwt\Controller\Wso2AjaxController
 */

namespace Drupal\wso2_with_jwt\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Language\LanguageInterface;
use Drupal\wso2_with_jwt\PizzaShackConnection;
use Drupal\file\Entity\File;

class Wso2AjaxController extends ControllerBase
{

  /**
   * Mostra la pagina di gestione affollamento POI
   */
  public function testpage()
  {

    $id_utente = \Drupal::currentUser()->id();
    $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $tempstore = \Drupal::service('user.private_tempstore')->get('wso2_with_jwt');

    $preferenze = [];
    // $preferenze['id_utente'] = $id_utente;
    // $preferenze['id_token'] =  $tempstore->get('WSO2_JWT');

    return [
      '#theme' => 'wso2testpage',
      '#attached' => [
        'library' => [
          'wso2_with_jwt/wso2-with-jwt-styles',
        ]
      ],
      '#utente' => $preferenze,
    ];
  }

  public function callApiManager()
  {
    # New responses
    $checkThisOut = [];
    $response = new AjaxResponse();
    $statusCode = Response::HTTP_BAD_REQUEST;

    $connection = new PizzaShackConnection();
    $checkThisOut = $connection->getMenu();
    $statusCode = Response::HTTP_OK;


    # Commands Ajax
    $response->setStatusCode($statusCode);
    $response->setData($checkThisOut);

    # Return response
    return $response;
  }
}
