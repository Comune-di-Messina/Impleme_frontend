<?php

/**
 * @file
 * Implements preprocess functions for six8theme theme.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements template_preprocess_html().
 */
function six8theme_preprocess_html(&$variables) {
  global $base_url;
  $variables['base_url'] = $base_url;

  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node) {
    $variables['attributes']['class'][] = 'node-' . $node->id();
  }
}

/**
 * Implements template_preprocess_page.
 */
function six8theme_preprocess_page(&$variables) {
  global $base_url;
  $variables['base_url'] = $base_url;
}

/**
 * Implements template_preprocess_page.
 */
function six8theme_preprocess_region(&$variables) {
  global $base_url;
  $variables['base_url'] = $base_url;
}

/**
 * Implements template_preprocess_views_view.
 */
function six8theme_preprocess_views_view(&$variables) {

}

/**
 * Implements template_preprocess_block().
 */
function six8theme_preprocess_block(&$variables) {

}

/**
 * Implements template_preprocess_entity.
 */
function six8theme_preprocess_entity(&$variables) {
  six8theme_entity_base_classes($variables);
}

/**
 * Implements template_preprocess_layout__onecol().
 */
function six8theme_preprocess_layout__onecol(&$variables) {
  six8theme_entity_base_classes($variables);
}

/**
 * Implements template_preprocess_field().
 */
function six8theme_preprocess_field(&$variables) {
  if (isset($variables['element']['#view_mode'])) {
    $variables['attributes']['class'][] = 'view-mode-' . Html::cleanCssIdentifier($variables['element']['#view_mode']);
  }
}

/**
 * Implements template_preprocess_menu().
 */
function six8theme_preprocess_menu(&$variables) {
  $menu_level = 1;
  foreach ($variables["items"] as $id => &$item) {
    _six8theme_set_menu_item_attributes($item, $menu_level);
  }
}

/**
 * Set attributes on main menu links.
 *
 * @param array $item
 *   Menu link array.
 * @param int $level
 *   Menu level.
 */
function _six8theme_set_menu_item_attributes(array &$item, $level) {
  $current_level = $level++;
  $item["attributes"]->addClass('menu-level-' . $current_level);
  if (!empty($item["below"])) {
    foreach ($item["below"] as $id => &$item_below) {
      _six8theme_set_menu_item_attributes($item_below, $level);
    }
  }
}

/**
 * Set base entities classes.
 *
 * @param array $variables
 *   The $variables array.
 */
function six8theme_entity_base_classes(array &$variables) {
  if (isset($variables['content']['#entity'])) {
    $entity_type = Html::cleanCssIdentifier($variables["content"]["#entity_type"]);

    if (isset($variables["content"]["#view_mode"])) {
      $classes[] = $entity_type . '-view-mode-' . Html::cleanCssIdentifier($variables["content"]["#view_mode"]);
    }

    $classes[] = $entity_type . '-' . Html::cleanCssIdentifier($variables["content"]["#entity"]->bundle());
    $classes[] = $entity_type . '-' . $variables['content']['#entity']->ID();
  }

  if (!empty($classes)) {
    if (!empty($variables['attributes']['class'])) {
      $variables['attributes']['class'] = array_merge($variables['attributes']['class'], $classes);
    }
    else {
      $variables['attributes']['class'] = $classes;
    }
  }
}
