<?php

namespace Drupal\social_feed_fetcher\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SocialPostTypeForm.
 */
class SocialPostTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $social_post_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $social_post_type->label(),
      '#description' => $this->t("Label for the Social post type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $social_post_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\social_feed_fetcher\Entity\SocialPostType::load',
      ],
      '#disabled' => !$social_post_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $social_post_type = $this->entity;
    $status = $social_post_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Social post type.', [
          '%label' => $social_post_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Social post type.', [
          '%label' => $social_post_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($social_post_type->toUrl('collection'));
  }

}
