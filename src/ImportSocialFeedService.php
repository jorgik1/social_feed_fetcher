<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\State\State;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportSocialFeedService.
 *
 * @package Drupal\social_feed_fetcher
 */
class ImportSocialFeedService implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Config\Config
   */
  private $config;

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queue;

  /**
   * @var \Drupal\social_feed_fetcher\SocialDataProviderManager
   */
  private $socialDataProvider;

  /**
   * @var \Drupal\Core\State\State
   */
  private $state;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * ImportSocialFeedService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   * @param \Drupal\social_feed_fetcher\SocialDataProviderManager $socialDataProviderManager
   * @param \Drupal\Core\State\State $state
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   */
  public function __construct(ConfigFactory $config_factory, QueueFactory $queueFactory, SocialDataProviderManager $socialDataProviderManager, State $state, LoggerInterface $logger, MessengerInterface $messenger) {
    $this->config = $config_factory->getEditable('social_feed_fetcher.settings');
    $this->queue = $queueFactory;
    $this->socialDataProvider = $socialDataProviderManager;
    $this->state = $state;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * Pull social feeds, as configured by module settings.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   */
  public function import() {

    $interval = $this->config->get('social_feed_fetcher_interval');
    $request_time = \Drupal::time()->getRequestTime();

    // Check if we need to run the cron yet.
    $next_execution = $this->state->get('social_feed_fetcher.next_execution');
    $next_execution = !empty($next_execution) ? $next_execution : 0;

    if ($request_time >= $next_execution) {
      $this->doImport();

      if ($this->state->get('social_feed_fetcher_show_status_message')) {
        $this->messenger->addMessage(t('The Social Feed Fetcher cron executed at %time', ['%time' => date('c', $request_time)]));
        $this->state->set('social_feed_fetcher_show_status_message', FALSE);
      }

      $this->state->set('social_feed_fetcher.next_execution', $request_time + $interval);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('queue'),
      $container->get('plugin.social_data_provider.manager'),
      $container->get('state'),
      $container->get('social_feed_fetcher.logger'),
      $container->get('messenger')
    );
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function doImport() {
    $facebook_count = 0;
    if ($this->config->get('facebook_enabled') === TRUE) {
      $facebook_count = $this->doFacebookImport();
    }
    // Get twitter posts, if enabled.
    $twitter_count = 0;
    if ($this->config->get('twitter_enabled') === TRUE) {
      $twitter_count = $this->doTweeterImport();
    }

    // Get instagram posts, if enabled.
    $instagram_count = 0;
    if ($this->config->get('instagram_enabled') === TRUE) {
      $instagram_count = $this->doInstagrammImport();
    }

    $linkedin_count = 0;
    if ($this->config->get('linkedin_enabled') === TRUE) {
      $this->doLinkedinImport();
    }

    $this->logger
      ->info('Social Feed Fetcher ran. Facebook @facebook. Twitter @twitter. Instagram @instagram. Linkedin @linkedin', [
        '@facebook' => $this->config->get('facebook_enabled') ? ' enabled, ' . $facebook_count . ' new posts' : ' disabled',
        '@twitter' => $this->config->get('twitter_enabled') ? ' enabled, ' . $twitter_count . ' new posts' : ' disabled',
        '@instagram' => $this->config->get('instagram_enabled') ? ' enabled, ' . $instagram_count . ' new posts' : ' disabled',
        '@linkedin' => $this->config->get('linkedin_enabled') ? ' enabled, ' . $linkedin_count . ' new posts' : ' disabled'
      ]);
  }

  /**
   * @return int|void
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function doFacebookImport() {
    // Get facebook posts, if enabled.
    $facebook_count = 0;
    /** @var \Drupal\Core\Queue\QueueInterface $facebook_queue */
    $facebook_queue = $this->queue->get('social_posts_facebook_queue_worker');
    /** @var \Drupal\social_feed_fetcher\Plugin\SocialDataProvider\FacebookDataProvider $facebook */
    $facebook = $this->socialDataProvider->createInstance('facebook');
    $facebook->setClient();
    $posts_count_num = $this->config->get('fb_no_feeds');
    // Get all recent posts facebook.
    try {
      $posts = $facebook->getPosts($posts_count_num);
    } catch (Exception $exception) {
      $this->logger->error('Error message was occurring while posts import. ' . $exception->getMessage());
      $this->messenger->addError($facebook->getPluginId() . ' ' . $exception->getMessage());
      return;
    }
    foreach ($posts as $item) {
      $facebook_queue->createItem($item);
      $facebook_count++;
    }
    return $facebook_count;
  }

  /**
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function doTweeterImport() {
    $twitter_count = 0;
    /** @var \Drupal\Core\Queue\QueueInterface $twitter_queue */
    $twitter_queue = $this->queue->get('social_posts_twitter_queue_worker');
    /** @var \Drupal\social_feed_fetcher\Plugin\SocialDataProvider\TwitterDataProvider $twitter */
    $twitter = $this->socialDataProvider->createInstance('twitter');
    $twitter->setTimelines($this->config->get('timeline'), $this->config->get('screen_name'));
    $twitter->setClient();
    try {
      $posts = $twitter->getPosts($this->config->get('tw_count'));
    } catch (Exception $exception) {
      $this->logger->error('Error message was occurring while posts import. ' . $exception->getMessage());
      $this->messenger->addError($twitter->getPluginId() . ' ' . $exception->getMessage());
    }
    foreach ($posts as $item) {
      $twitter_queue->createItem($item);
      $twitter_count++;
    }
    return $twitter_count;
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function doInstagrammImport() {
    $instagram_count = 0;
    /** @var \Drupal\Core\Queue\QueueInterface $instagram_queue */
    $instagram_queue = $this->queue->get('social_posts_instagram_queue_worker');
    /** @var \Drupal\social_feed_fetcher\Plugin\SocialDataProvider\InstagramDataProvider $instagram */
    $instagram = $this->socialDataProvider->createInstance('instagram');
    $instagram->setClient();
    try {
      $posts = $instagram->getPosts($this->config->get('in_picture_count'));
    } catch (Exception $exception) {
      $this->logger->error('Error message was occurring while posts import. ' . $exception->getMessage());
      $this->messenger->addError($instagram->getPluginId() . ' ' . $exception->getMessage());
    }
    foreach ($posts as $item) {
      $instagram_queue->createItem($item);
      $instagram_count++;
    }
    return $instagram_count;
  }

  /**
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function doLinkedinImport() {
    $linkedin_count = 0;
    $linkedin_posts = [];
    /** @var \Drupal\Core\Queue\QueueInterface $linkedin_queue */
    $linkedin_queue = $this->queue->get('social_posts_linkedin_queue_worker');
    /** @var \Drupal\social_feed_fetcher\Plugin\SocialDataProvider\LinkedinDataProvider $linkedin */
    $linkedin = $this->socialDataProvider->createInstance('linkedin');
    $linkedin->setFeed($this->config->get('linkedin_feed_type'));
    if ($this->config->get('linkedin_feed_type') === 'companies'){
      $linkedin->setCompaniesId($this->config->get('linkedin_companies_id'));
    }
    $linkedin->setClient();
    try {
      $linkedin_posts = $linkedin->getPosts($this->config->get('linkedin_posts_count'));
    } catch (Exception $exception) {
      $this->logger->error('Error message was occurring while posts import. ' . $exception->getMessage());
      $this->messenger->addError($linkedin->getPluginId() . ' ' . $exception->getMessage());
    }
    if ($linkedin_posts) {
      foreach ($linkedin_posts['values'] as $item) {
        if (!is_array($item)) {
          continue;
        }
        $linkedin_queue->createItem($item);
        $linkedin_count++;
      }
    }
    return $linkedin_count;
  }
}
