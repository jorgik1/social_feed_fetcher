<?php

namespace Drupal\social_feed_fetcher\Plugin\NodeProcessor;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\social_feed_fetcher\PluginNodeProcessorPluginBase;

/**
 * Class TwitterNodeProcessor
 *
 * @package Drupal\social_feed_fetcher\Plugin\NodeProcessor
 *
 * @PluginNodeProcessor(
 *   id = "twitter_processor",
 *   label = @Translation("Twitter node processor")
 * )
 */
class TwitterNodeProcessor extends PluginNodeProcessorPluginBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($source, $data_item) {
    $query = $this->entityStorage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'social_post')
      ->condition('field_id', $data_item->id);
    $entity_ids = $query->execute();
    if (empty($entity_ids)) {
      $time = new DrupalDateTime($data_item->created_at);
      /** @var \Drupal\Core\Datetime\DrupalDateTime $time */
      $time->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
      $string = $time->format(DATETIME_DATETIME_STORAGE_FORMAT);
      $node = $this->entityStorage->create([
        'type' => 'social_post',
        'title' => 'Post ID: ' . $data_item->id,
        'field_platform' => ucwords($source),
        'field_id' => $data_item->id,
        'field_post' => [
          'value' => social_feed_fetcher_linkify(html_entity_decode($data_item->text)),
          'format' => $this->config->get('formats.post_format'),
        ],
        'field_link' => [
          'uri' => $data_item->entities->urls[0]->url,
          'title' => '',
          'options' => [],
        ],
        'field_sp_image' => [
          'target_id' => social_feed_fetcher_save_file($data_item->entities->media[0]->media_url_https, 'public://twitter/'),
        ],
        'field_posted' => $string,
      ]);
      return $node->save();
    }
    return FALSE;
  }

  /**
   * Setter for entityStorage
   *
   * @param $enitytStorage
   *
   * @return $this
   */
  public function setStorage($enitytStorage) {
    $this->entityStorage = $enitytStorage;
    return $this;
  }

  /**
   * Setter for Config.
   *
   * @param $config
   *
   * @return $this
   */
  public function setConfig($config) {
    $this->config = $config;
    return $this;
  }

}