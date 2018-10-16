<?php

namespace Drupal\social_feed_fetcher\Plugin\NodeProcessor;

use Drupal\social_feed_fetcher\PluginNodeProcessorPluginBase;

/**
 * Class LinkedinNodeProcessor
 *
 * @package Drupal\social_feed_fetcher\Plugin\NodeProcessor
 *
 * @PluginNodeProcessor(
 *   id = "linkedin_processor",
 *   label = @Translation("Linkedin node processor")
 * )
 */
class LinkedinNodeProcessor extends PluginNodeProcessorPluginBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($source, $data_item) {
    $item = $data_item['updateContent']['companyStatusUpdate']['share'];
    if (!is_array($item)) {
      return FALSE;
    }
    if (!$this->isPostIdExist($item['id'])) {

      $file = NULL;
      if (isset($item['content'])) {
        $file = social_feed_fetcher_save_file($item['content']['eyebrowUrl'], 'public://linkedin');
      }

      $node = $this->entityStorage->create([
        'type' => 'social_post',
        'title' => 'Post ID: ' . $item['id'],
        'field_platform' => ucwords($source),
        'field_id' => $item['id'],
        'field_post' => [
          'value' => social_feed_fetcher_linkify(html_entity_decode($item['comment'] ?: '')),
          'format' => $this->config->get('formats_post_format'),
        ],
        'field_social_feed_link' => [
          'uri' => isset($item['link']) ? $item['link'] : '',
          'title' => '',
          'options' => [],
        ],
        'field_sp_image' => [
          'target_id' => $file,
        ],
        'field_posted' => [
          'value' => $this->setPostTime('now'),
        ],
      ]);
      return $node->save();
    }
    return FALSE;
  }

}
