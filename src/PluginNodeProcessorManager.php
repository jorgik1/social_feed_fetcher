<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\social_feed_fetcher\Annotation\PluginNodeProcessor;
use GuzzleHttp\ClientInterface;

/**
 * Provides an NodeProcessor plugin manager.
 */
class PluginNodeProcessorManager extends DefaultPluginManager {

  /**
   * Drupal\Core\Config\Config definition.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Drupal\Core\Entity\EntityStorageInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  protected $entityStorage;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
  * @var \GuzzleHttp\ClientInterface
  */
  protected $httpClient;

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler,  ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager, ClientInterface $httpClient, FileSystemInterface $file_system) {
    parent::__construct(
      'Plugin/NodeProcessor',
      $namespaces,
      $module_handler,
      PluginNodeProcessorPluginInterface::class,
      PluginNodeProcessor::class
    );
    # hook_node_processor_info_alter();
    $this->alterInfo('node_processor_info');
    $this->setCacheBackend($cache_backend, 'node_processor');
    $this->factory = new DefaultFactory($this->getDiscovery());
    $this->config = $configFactory->getEditable('social_feed_fetcher.settings');
    $this->entityStorage = $entityTypeManager->getStorage('node');
    $this->httpClient = $httpClient;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $instance = parent::createInstance($plugin_id, $configuration);
    $instance->setConfig($this->config);
    $instance->setStorage($this->entityStorage);
    $instance->setClient($this->httpClient);
    $instance->setFileSystem($this->fileSystem);
    return $instance;
  }

}
