<?php

namespace Drupal\social_feed_fetcher\Plugin\NodeProcessor;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\social_feed_fetcher\PluginNodeProcessorPluginBase;

/**
 * Class InstagramNodeProcessor.
 *
 * @package Drupal\social_feed_fetcher\Plugin\NodeProcessor
 *
 * @PluginNodeProcessor(
 *   id = "instagram_processor",
 *   label = @Translation("Instagram node processor")
 * )
 */
class InstagramNodeProcessor extends PluginNodeProcessorPluginBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($source, $data_item) {
    if (!$this->isPostIdExist($data_item['raw']->id)) {
      $time = new DrupalDateTime($data_item['raw']->timestamp);
      $time->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $string = $time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $node = $this->entityStorage->create([
        'type' => 'social_post',
        'title' => 'Post ID: ' . $data_item['raw']->id,
        'field_platform' => ucwords($source),
        'field_id' => $data_item['raw']->id,
        'field_post' => [
          'value' => social_feed_fetcher_linkify(html_entity_decode($data_item['raw']->caption)),
          'format' => $this->config->get('formats_post_format'),
        ],
        'field_social_feed_link' => [
          'uri' => $data_item['raw']->permalink,
          'title' => '',
          'options' => [],
        ],
        'field_sp_image' => [
          'target_id' => $this->processImageFile($data_item['raw']->media_url, 'public://instagram'),
        ],
        'field_posted' => [
          'value' => $string,
        ],
      ]);
      return $node->save();
    }
    return FALSE;
  }

  /**
   * Save external file.
   *
   * @param string $filename
   *   File name.
   * @param string $path
   *   Current path.
   *
   * @return int
   *   Id of the file entity.
   */
  public function processImageFile($filename, $path) {
    $name = basename($filename);
    $response = $this->httpClient->get($filename);
    $data = $response->getBody();
    $uri = $path . '/' . $name;
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    $uri = explode('?', $uri);
    return file_save_data($data, $uri[0], FileSystemInterface::EXISTS_REPLACE)->id();
  }

}
