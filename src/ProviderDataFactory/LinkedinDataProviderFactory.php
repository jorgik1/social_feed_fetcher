<?php


namespace Drupal\social_feed_fetcher\ProviderDataFactory;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LinkedinDataProviderFactory {

  /**
   * @var \Drupal\Core\Config\Config
   */
  private $config;

  /**
   * LinkedinClientFactory constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('social_feed_fetcher.settings');
  }


  public function createLinkedinClient() {
    return new \LinkedIn\Client(
      $this->config->get('linkedin_client_id'),
      $this->config->get('linkedin_secret_app')
    );
  }
}