<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Component\Plugin\PluginBase;

abstract class PluginNodeProcessorPluginBase extends PluginBase implements PluginNodeProcessorPluginInterface {


  /**
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  protected $entityStorage;

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

  /**
   * {@inheritdoc}
   */
  public function processItem($source, $data_item) {
    return TRUE;
  }

}