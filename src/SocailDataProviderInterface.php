<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface SocailDataProviderInterface extends PluginInspectionInterface {

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
   */
  public function getPosts();

}