<?php

namespace Drupal\social_feed_fetcher\Plugin\SocialDataProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use MetzWeb\Instagram\Instagram;

/**
 * Class InstagramPostCollector.
 *
 * @package Drupal\socialfeed
 */
class InstagramDataProvider {

  /**
   * Instagram application api key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * Instagram application access token.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * Instagram client.
   *
   * @var \MetzWeb\Instagram\Instagram
   */
  protected $instagram;

  /**
   * InstagramPostCollector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \MetzWeb\Instagram\Instagram|null $instagram
   *   Instagram client.
   */
  public function __construct(ConfigFactoryInterface $configFactory, Instagram $instagram = NULL) {
    $config            = $configFactory->get('socialfeed.instagramsettings');
    $this->apiKey      = $config->get('client_id');
    $this->accessToken = $config->get('access_token');

    $this->instagram = $instagram;
  }

  /**
   * Set the Instagram client.
   */
  public function setInstagramClient() {
    if (NULL === $this->instagram) {
      $this->instagram = new Instagram($this->apiKey);
      $this->instagram->setAccessToken($this->accessToken);
    }
  }

  /**
   * Retrieve user's posts.
   *
   * @param int $numPosts
   *   Number of posts to get.
   * @param string $resolution
   *   The resolution to get.
   *
   * @return array
   *   An array of stdClass posts.
   */
  public function getPosts($numPosts, $resolution) {
    $posts    = [];
    $response = $this->instagram->getUserMedia('self', $numPosts);
    if (isset($response->data)) {
      $posts = array_map(function ($post) use ($resolution) {
        $type = $this->getMediaArrayKey($post->type);
        return [
          'raw'       => $post,
          'media_url' => isset($post->{$type}->{$resolution}) ? $post->{$type}->{$resolution}->url : '',
          'type'      => $post->type,
        ];
      }, $response->data);
    }
    return $posts;
  }

  /**
   * Retrieve the array key to fetch the post media url.
   *
   * @param string $type
   *   The post type.
   *
   * @return string
   *   The array key to fetch post media url.
   */
  protected function getMediaArrayKey($type) {
    $mediaType = 'images';
    if ($type === 'video') {
      $mediaType = 'videos';
    }
    return $mediaType;
  }

}
