<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use GuzzleHttp\ClientInterface;

/**
 * PluginNodeProcessorPluginBase class.
 */
abstract class PluginNodeProcessorPluginBase extends PluginBase implements PluginNodeProcessorPluginInterface {


  /**
   * Configuration definition.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * EntityStorageInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  protected $entityStorage;

  /**
   * Guzzle client definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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

  /**
   * Helper function to check if post with ID doesn't exist.
   *
   * @param int $data_item_id
   *   Id from source.
   *
   * @return array|int
   *   Return array of ids, otherwise empty array.
   */
  public function isPostIdExist($data_item_id) {
    if (!$data_item_id) {
      return FALSE;
    }
    $query = $this->entityStorage->getQuery()
      ->condition('type', 'social_post')
      ->condition('field_id', $data_item_id);
    return $query->execute();
  }

  /**
   * Helper function for getting Drupal based time entry.
   *
   * @param string $time_entry
   *   Time format from social network.
   *
   * @return string
   *   Formatted time string.
   */
  public function setPostTime($time_entry) {
    $time = new DrupalDateTime($time_entry);
    $time->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
    return $time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

  /**
   * Setter for entityStorage.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $enitytStorage
   *   EntityStorageInterface definition.
   *
   * @return \Drupal\social_feed_fetcher\PluginNodeProcessorPluginBase
   *   Plugin definition.
   */
  public function setStorage(EntityStorageInterface $enitytStorage) {
    $this->entityStorage = $enitytStorage;
    return $this;
  }

  /**
   * Setter for Config.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Config definition.
   *
   * @return $this
   */
  public function setConfig(Config $config) {
    $this->config = $config;
    return $this;
  }

  /**
   * Setter for httpClient.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   Client definition.
   *
   * @return $this
   */
  public function setClient(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
    return $this;
  }

  /**
   * Get FileSystemInterface.
   *
   * @return \Drupal\Core\File\FileSystemInterface
   *   FileSystemInterface definition.
   */
  public function getFileSystem() {
    return $this->fileSystem;
  }

  /**
   * Setter for FileSystemInterface.
   *
   * @param mixed $fileSystem
   *   FileSystemInterface definition.
   */
  public function setFileSystem($fileSystem) {
    $this->fileSystem = $fileSystem;
  }

}
