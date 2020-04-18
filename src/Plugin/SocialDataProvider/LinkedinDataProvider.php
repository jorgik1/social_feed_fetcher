<?php

namespace Drupal\social_feed_fetcher\Plugin\SocialDataProvider;

use Drupal\social_feed_fetcher\SocialDataProviderPluginBase;
use LinkedIn\AccessToken;

/**
 * Class LinkedinDataProvider.
 *
 * @package Drupal\social_feed_fetcher\Plugin\SocialDataProvider
 *
 * @SocialDataProvider(
 *   id = "linkedin",
 *   label = @Translation("Linkedin data provider")
 * )
 */
class LinkedinDataProvider extends SocialDataProviderPluginBase {

  /**
   * Twitter OAuth client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $linkedin;

  /**
   * Feed used to get posts.
   *
   * @var string
   */
  protected $feed;

  /**
   * The companies Id to get update.
   *
   * @var string
   */
  protected $companiesId;

  /**
   * Set the Twitter client.
   */
  public function setClient() {
    if (NULL === $this->linkedin) {
      $this->client = \Drupal::service('social_feed_fetcher.linkedin.client');
    }
  }

  /**
   * @param $feed
   */
  public function setFeed($feed) {
    $this->feed = $feed;
  }

  /**
   * @param $companiesId
   */
  public function setCompaniesId($companiesId) {
    $this->companiesId = $companiesId;
  }

  /**
   * Retrieve Posts from the given accounts home page.
   *
   * @param int $count
   *   The number of posts to return.
   *
   * @return array
   *   An array of posts.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getPosts($count) {
    $access = \Drupal::state()->getMultiple(['access_token', 'expires_in', 'expires_in_save']);
    $this->client->setApiHeaders([
      'Content-Type' => 'application/json',
      'x-li-format' => 'json',
    // Use protocol v2.
      'X-Restli-Protocol-Version' => '2.0.0',
    ]);
    $this->client->setApiRoot('https://api.linkedin.com/v2/');
    $this->client->setAccessToken(new AccessToken($access['access_token']));
    $feed = $this->feed;
    if ($this->feed === 'companies') {
      $feed = "ugcPosts?q=authors&authors=List(urn%3Ali%3Aorganization%3A{$this->companiesId})";
    }
    if ($this->feed === 'people') {
      $feed = 'me';
    }
    return $this->client->get($feed);
  }

}
