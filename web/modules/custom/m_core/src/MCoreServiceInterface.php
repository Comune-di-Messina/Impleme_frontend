<?php

namespace Drupal\m_core;

/**
 * Interface MCoreServiceInterface.
 */
interface MCoreServiceInterface {

  /**
   * Add path luoghi to variables luoghi_path.
   *
   * @param array $variables
   *   Variables where add luoghi_path entry.
   */
  public function setVariablesButtonLearnMore(array &$variables);

  /**
   * Retrieve Current Node from Request.
   */
  public function retrieveCurrentNodeFromRequest();

  /**
   * Return nid of field comune.
   *
   * @param object $node
   *   Current page Node.
   *
   * @return null|string
   *   Return nid of comune
   */
  public function getNidComune($node);

  /**
   * Retrieve Menu and return Referenced Entity in field_categoria_aggregatore.
   *
   * @return array
   *   Return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getMenuReferencedEntity(): array;

  /**
   * Retrieve logo from comune.
   *
   * @param mixed $variables
   *
   *   Variables Drupal.
   * @param object $comune
   *   Node Landing Comune.
   */
  public function retrieveLogoByCommune(&$variables, $comune);

}
