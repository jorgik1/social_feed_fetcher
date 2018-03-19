<?php

namespace Drupal\social_feed_fetcher\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Social post entities.
 *
 * @ingroup social_feed_fetcher
 */
interface SocialPostInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Social post name.
   *
   * @return string
   *   Name of the Social post.
   */
  public function getName();

  /**
   * Sets the Social post name.
   *
   * @param string $name
   *   The Social post name.
   *
   * @return \Drupal\social_feed_fetcher\Entity\SocialPostInterface
   *   The called Social post entity.
   */
  public function setName($name);

  /**
   * Gets the Social post creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Social post.
   */
  public function getCreatedTime();

  /**
   * Sets the Social post creation timestamp.
   *
   * @param int $timestamp
   *   The Social post creation timestamp.
   *
   * @return \Drupal\social_feed_fetcher\Entity\SocialPostInterface
   *   The called Social post entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Social post published status indicator.
   *
   * Unpublished Social post are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Social post is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Social post.
   *
   * @param bool $published
   *   TRUE to set this Social post to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\social_feed_fetcher\Entity\SocialPostInterface
   *   The called Social post entity.
   */
  public function setPublished($published);

}
