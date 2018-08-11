<?php

namespace Drupal\social_feed_fetcher\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\social_feed_fetcher\PluginNodeProcessorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *  id = "social_posts_twitter_queue_worker",
 *  title = @Translation("Social Posts Queue Worker"),
 *  cron = {"time" = 10},
 * )
 */
class SocialPostTwitterQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\social_feed_fetcher\PluginNodeProcessorManager
   */
  public $nodeProcessor;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginNodeProcessorManager $nodeProcessor) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeProcessor = $nodeProcessor;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var \Drupal\social_feed_fetcher\Plugin\NodeProcessor\TwitterNodeProcessor $twitter_node_processor */
    $twitter_node_processor = $this->nodeProcessor->createInstance('twitter_processor');
    $twitter_node_processor->processItem('twitter', $data);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.node_processor'));
  }

}
