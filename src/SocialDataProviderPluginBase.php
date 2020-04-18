<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Core\Config\Config;
use Drupal\Core\Plugin\PluginBase;

/**
 * SocialDataProviderPluginBase class.
 */
abstract class SocialDataProviderPluginBase extends PluginBase implements SocialDataProviderInterface {

  /**
   * Configuration definition.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;


  /**
   * Social network client object.
   *
   * @var object
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * Setter for Config.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Configuration definition.
   *
   * @return $this
   */
  public function setConfig(Config $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  abstract public function getPosts($count);

  /**
   * {@inheritdoc}
   */
  abstract public function setClient();

}
