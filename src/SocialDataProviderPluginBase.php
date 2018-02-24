<?php

namespace Drupal\social_feed_fetcher;


use Drupal\Core\Plugin\PluginBase;

abstract class SocialDataProviderPluginBase extends PluginBase implements SocailDataProviderInterface  {

  /**
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Social network client object.
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

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

  abstract public function getPosts();

  abstract public function setClient();
}