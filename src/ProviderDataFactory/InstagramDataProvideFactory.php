<?php

namespace Drupal\social_feed_fetcher\ProviderDataFactory;

use Drupal\Core\Config\ConfigFactoryInterface;
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;

/**
 *
 */
class InstagramDataProvideFactory {

  private $config;

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('social_feed_fetcher.settings');
  }

  /**
   *
   */
  public function create() {
    return new InstagramBasicDisplay(
      [
        'appId' => $this->config->get('in_client_id'),
        'appSecret' => $this->config->get('in_client_secret'),
        'redirectUri' => \Drupal::request()->getScheme() . '://' . \Drupal::request()->getHost() . '/instagram/oauth/callback',
      ]
    );
  }

}
