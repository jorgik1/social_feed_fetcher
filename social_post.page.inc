<?php

/**
 * @file
 * Contains social_post.page.inc.
 *
 * Page callback for Social post entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Social post templates.
 *
 * Default template: social_post.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_social_post(array &$variables) {
  // Fetch SocialPost Entity Object.
  $social_post = $variables['elements']['#social_post'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
