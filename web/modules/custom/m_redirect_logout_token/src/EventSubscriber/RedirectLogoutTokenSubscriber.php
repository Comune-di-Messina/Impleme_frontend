<?php

namespace Drupal\m_redirect_logout_token\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectLogoutTokenSubscriber.
 */
class RedirectLogoutTokenSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempstorePrivate;

  /**
   * Constructs a new RedirectLogoutTokenSubscriber object.
   */
  public function __construct(PrivateTempStoreFactory $tempstore_private) {
    $this->tempstorePrivate = $tempstore_private;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['checkToken'];

    return $events;
  }

  /**
   * This method is called when the kernel.request is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function checkToken(Event $event) {
    $tempstore = $this->tempstorePrivate->get('redirectToLogout');
    if ($tempstore->get('redirect')) {
      user_logout();
      $response = new RedirectResponse('/');
      $response->send();
    }
  }

}
