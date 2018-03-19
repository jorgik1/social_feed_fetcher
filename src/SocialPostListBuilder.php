<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Social post entities.
 *
 * @ingroup social_feed_fetcher
 */
class SocialPostListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Social post ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\social_feed_fetcher\Entity\SocialPost */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.social_post.edit_form',
      ['social_post' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
