<?php

namespace Drupal\social_feed_fetcher\Plugin\SocialDataProvider;

use Drupal\social_feed_fetcher\SocialDataProviderPluginBase;

/**
 * Class InstagramDataProvider.
 *
 * @package Drupal\social_feed_fetcher\Plugin\SocialDataProvider
 *
 * @SocialDataProvider(
 *   id = "instagram",
 *   label = @Translation("Instagram data provider")
 * )
 */
class InstagramDataProvider extends SocialDataProviderPluginBase {

  /**
   * Instagram client.
   *
   * @var \EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay
   */
  protected $instagram;

  /**
   * Set the Instagram client.
   *
   * @throws \Exception
   */
  public function setClient() {
    if (NULL === $this->instagram) {
      $this->instagram = \Drupal::service('social_feed_fetcher.instagram.client');
      $token = $this->instagram->getLongLivedToken(\Drupal::service('state')->get('insta_access_token'));
      $this->instagram->setAccessToken($token->access_token);
    }
  }

  /**
   * Retrieve user's posts.
   *
   * @param int $numPosts
   *   Number of posts to get.
   *
   * @return array
   *   An array of stdClass posts.
   */
  public function getPosts($numPosts) {
    $posts    = [];
    $response = $this->instagram->getUserMedia('me', $numPosts);
    if (isset($response->data)) {
      $posts = array_map(static function ($post) {
        return [
          'raw'       => $post,
          'media_url' => $post->media_url ?? '',
          'type'      => $post->media_type,
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
