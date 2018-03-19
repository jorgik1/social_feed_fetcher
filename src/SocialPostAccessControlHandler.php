<?php

namespace Drupal\social_feed_fetcher;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Social post entity.
 *
 * @see \Drupal\social_feed_fetcher\Entity\SocialPost.
 */
class SocialPostAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\social_feed_fetcher\Entity\SocialPostInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished social post entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published social post entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit social post entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete social post entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add social post entities');
  }

}
