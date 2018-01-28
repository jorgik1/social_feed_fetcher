<?php

namespace Drupal\social_feed_fetcher\Plugin\NodeProcessor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\social_feed_fetcher\PluginNodeProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InstagramNodeProcessor
 * @package Drupal\social_feed_fetcher\Plugin\NodeProcessor
 *
 * @PluginNodeProcessor(
 *   id = "instagram_processor",
 *   label = @Translation("Instagram node processor")
 * )
 */
class InstagramNodeProcessor extends PluginNodeProcessorPluginBase {

  public function processItem($source, $data_item) {
    $query = $this->entityStorage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'social_post')
      ->condition('field_id', $data_item['raw']->id);
    $entity_ids = $query->execute();
    if (empty($entity_ids)) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $time */
      $time = new \Drupal\Core\Datetime\DrupalDateTime();
      $time->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
      $time->setTimestamp($data_item['raw']->created_time);
      $string = $time->format(DATETIME_DATETIME_STORAGE_FORMAT);
      $node = Node::create([
        'type' => 'social_post',
        'title' => 'Post ID: ' . $data_item['raw']->id,
        'field_platform' => ucwords($source),
        'field_id' => $data_item['raw']->id,
        'field_post' => [
          'value' => social_feed_fetcher_linkify(html_entity_decode($data_item['raw']->caption->text)),
          'format' => $this->config->get('formats.post_format'),
        ],
        'field_link' => [
          'uri' => $data_item['raw']->link,
          'title' => '',
          'options' => [],
        ],
        'field_sp_image' => [
          'target_id' => social_feed_fetcher_save_file($data_item['media_url'],'public://instagram/'),
        ],
        'field_posted' => [
          'value' => $string
        ],
      ]);
      $node->save();
      return TRUE;
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
  public function setStorage($enitytStorage){
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
  public function setConfig($config){
    $this->config = $config;
    return $this;
  }

}
