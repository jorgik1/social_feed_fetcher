<?php

namespace Drupal\social_feed_fetcher\Plugin\SocialDataProvider;


use Drupal\social_feed_fetcher\Annotation\SocialDataProvider;
use Drupal\social_feed_fetcher\SocialDataProviderPluginBase;

/**
 * Class FacebookDataProvider
 *
 * @package Drupal\social_feed_fetcher\Plugin\SocialDataProvider
 *
 * @SocialDataProvider(
 *   id = "facebook",
 *   label = @Translation("Facebook data provider")
 * )
 */
class FacebookDataProvider extends SocialDataProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPosts($count) {
    // TODO: Implement getPosts() method.
  }

  /**
   * {@inheritdoc}
   */
  public function setClient() {
    // TODO: Implement setClient() method.
  }
}