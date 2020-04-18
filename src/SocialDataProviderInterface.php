<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * SocialDataProviderInterface definition.
 */
interface SocialDataProviderInterface extends PluginInspectionInterface {

  /**
   * Getting ID.
   */
  public function getId();

  /**
   * Getting Label.
   */
  public function getLabel();

  /**
   * Setting social network client.
   */
  public function setClient();

  /**
   * Getting posts from social network.
   *
   * @param int $count
   *   Posts count parameter.
   *
   * @return array
   *   Posts array.
   */
  public function getPosts($count);

}
