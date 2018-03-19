<?php

namespace Drupal\social_feed_fetcher\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Social post type entity.
 *
 * @ConfigEntityType(
 *   id = "social_post_type",
 *   label = @Translation("Social post type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\social_feed_fetcher\SocialPostTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_feed_fetcher\Form\SocialPostTypeForm",
 *       "edit" = "Drupal\social_feed_fetcher\Form\SocialPostTypeForm",
 *       "delete" = "Drupal\social_feed_fetcher\Form\SocialPostTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\social_feed_fetcher\SocialPostTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "social_post_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "social_post",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/social_post_type/{social_post_type}",
 *     "add-form" = "/admin/structure/social_post_type/add",
 *     "edit-form" = "/admin/structure/social_post_type/{social_post_type}/edit",
 *     "delete-form" = "/admin/structure/social_post_type/{social_post_type}/delete",
 *     "collection" = "/admin/structure/social_post_type"
 *   }
 * )
 */
class SocialPostType extends ConfigEntityBundleBase implements SocialPostTypeInterface {

  /**
   * The Social post type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Social post type label.
   *
   * @var string
   */
  protected $label;

}
