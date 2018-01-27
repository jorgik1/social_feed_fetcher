<?php

namespace Drupal\social_feed_fetcher\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\CronInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form with examples on how to use cron.
 */
class SocialPostSettingsForm extends ConfigFormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The cron service.
   *
   * @var \Drupal\Core\CronInterface
   */
  protected $cron;

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user, CronInterface $cron, StateInterface $state) {
    parent::__construct($config_factory);
    $this->currentUser = $current_user;
    $this->cron = $cron;
    $this->state = $state;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('cron'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_feed_fetcher';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_feed_fetcher.settings');

    $next_execution = $this->state->get('social_feed_fetcher.next_execution');
    $next_execution = !empty($next_execution) ? $next_execution : REQUEST_TIME;

    $args = [
      '%time' => date_iso8601($this->state->get('social_feed_fetcher.next_execution')),
      '%seconds' => $next_execution - REQUEST_TIME,
    ];
    $form['status']['last'] = [
      '#type' => 'item',
      '#markup' => $this->t('The Social Feed Aggregator will next execute the first time the cron runs after %time (%seconds seconds from now)', $args),
    ];

    $form['facebook'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook settings'),
      '#open' => TRUE,
    ];

    $form['facebook']['facebook_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled?'),
      '#default_value' => $config->get('facebook.enabled'),
    ];

    $form['facebook']['facebook_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('facebook.username'),
      '#required' => $config->get('facebook.enabled') ? TRUE : FALSE,
    ];

    $form['facebook']['facebook_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('facebook.app_id'),
      '#required' => $config->get('facebook.enabled') ? TRUE : FALSE,
    ];

    $form['facebook']['facebook_app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#default_value' => $config->get('facebook.app_secret'),
      '#required' => $config->get('facebook.enabled') ? TRUE : FALSE,
      '#description' => $this->t('Obtain your Facebook app details by following the guide at <a href="@link">@link</a>', [
        '@link' => 'https://developers.facebook.com/docs/apps/register',
      ]),
    ];

    $form['twitter'] = [
      '#type' => 'details',
      '#title' => $this->t('Twitter settings'),
      '#open' => TRUE,
    ];

    $form['twitter']['twitter_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled?'),
      '#default_value' => $config->get('twitter.enabled'),
    ];

    $form['twitter']['twitter_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('twitter.username'),
      '#required' => $config->get('twitter.enabled') ? TRUE : FALSE,
    ];

    $form['twitter']['twitter_consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Key'),
      '#default_value' => $config->get('twitter.consumer_key'),
      '#required' => $config->get('twitter.enabled') ? TRUE : FALSE,
    ];

    $form['twitter']['twitter_consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Secret'),
      '#default_value' => $config->get('twitter.consumer_secret'),
      '#required' => $config->get('twitter.enabled') ? TRUE : FALSE,
    ];

    $form['twitter']['twitter_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#default_value' => $config->get('twitter.access_token'),
      '#required' => $config->get('twitter.enabled') ? TRUE : FALSE,
    ];

    $form['twitter']['twitter_access_token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token Secret'),
      '#default_value' => $config->get('twitter.access_token_secret'),
      '#required' => $config->get('twitter.enabled') ? TRUE : FALSE,
      '#description' => $this->t('Obtain your Twitter app details by following the guide at <a href="@link">@link</a>', [
        '@link' => 'https://dev.twitter.com/oauth/overview/application-owner-access-tokens',
      ]),
    ];

    $form['instagram'] = [
      '#type' => 'details',
      '#title' => $this->t('Instagram settings'),
      '#open' => TRUE,
    ];

    $form['instagram']['instagram_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled?'),
      '#default_value' => $config->get('instagram.enabled'),
    ];

    $form['instagram']['instagram_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('instagram.username'),
      '#required' => $config->get('instagram.enabled') ? TRUE : FALSE,
    ];

    $form['instagram']['instagram_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('instagram.client_id'),
      '#required' => $config->get('instagram.enabled') ? TRUE : FALSE,
    ];

    $form['instagram']['instagram_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#default_value' => $config->get('instagram.access_token'),
      '#required' => $config->get('instagram.enabled') ? TRUE : FALSE,
      '#description' => $this->t('Obtain your Instagram app details by following the guide at <a href="@link">@link</a>', [
        '@link' => 'https://www.instagram.com/developer/authentication/',
      ]),
    ];

    if ($this->currentUser->hasPermission('administer site configuration')) {
      $form['cron_run'] = [
        '#type' => 'details',
        '#title' => $this->t('Run cron manually'),
        '#open' => TRUE,
      ];
      $form['cron_run']['cron_trigger']['actions'] = ['#type' => 'actions'];
      $form['cron_run']['cron_trigger']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Run cron now'),
        '#submit' => [[$this, 'cronRun']],
      ];
    }

    $form['configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Schedule Cron'),
      '#open' => TRUE,
    ];
    $form['configuration']['social_feed_fetcher_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Cron interval'),
      '#description' => $this->t('Time after which cron will respond to a processing request.'),
      '#default_value' => $config->get('cron.interval'),
      '#options' => [
        60 => $this->t('1 minute'),
        300 => $this->t('5 minutes'),
        600 => $this->t('10 minutes'),
        900 => $this->t('15 minutes'),
        1800 => $this->t('30 minutes'),
        3600 => $this->t('1 hour'),
        21600 => $this->t('6 hours'),
        86400 => $this->t('1 day'),
      ],
    ];

    $allowed_formats = filter_formats();
    foreach (filter_formats() as $format_name => $format) {
       $allowed_formats[$format_name] = $format->label();
    }
    
    $form['formats'] = [
      '#type' => 'details',
      '#title' => $this->t('Post Format'),
      '#open' => TRUE,
    ];
   
    $form['formats']['formats_post_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Post format'),
      '#default_value' => $config->get('formats.post_format'),
      '#options' => $allowed_formats
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Extract the values submitted by the user.
    $values = $form_state->getValues();

    // Check user has provided all facebook details.
    if (!empty($values['facebook_enabled'])) {
      if (empty($values['facebook_username']) || empty($values['facebook_app_id']) || empty($values['facebook_app_secret'])) {
        $form_state->setErrorByName('facebook_enabled', $this->t('Please provide all Facebook details.'));
      }
    }

    // Check user has provided all twitter details.
    if (!empty($values['twitter_enabled'])) {
      if (empty($values['twitter_username']) || empty($values['twitter_consumer_key']) || empty($values['twitter_consumer_secret']) || empty($values['twitter_access_token']) || empty($values['twitter_access_token_secret'])) {
        $form_state->setErrorByName('twitter_enabled', $this->t('Please provide all Twitter details.'));
      }
    }

    // Check user has provided all instagram details.
    if (!empty($values['instagram_enabled'])) {
      if (empty($values['instagram_username']) || empty($values['instagram_client_id']) || empty($values['instagram_access_token'])) {
        $form_state->setErrorByName('instagram_enabled', $this->t('Please provide all Instagram details.'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * Allow user to directly execute cron, optionally forcing it.
   */
  public function cronRun(array &$form, FormStateInterface &$form_state) {

    // Use a state variable to signal that cron was run manually from this form.
    $this->state->set('social_feed_fetcher.next_execution', 0);
    $this->state->set('social_feed_fetcher_show_status_message', TRUE);
    if ($this->cron->run()) {
      drupal_set_message($this->t('Cron ran successfully.'));
    }
    else {
      drupal_set_message($this->t('Cron run failed.'), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('social_feed_fetcher.settings')
      ->set('cron.interval', $form_state->getValue('social_feed_fetcher_interval'))
      ->set('facebook.enabled', $form_state->getValue('facebook_enabled'))
      ->set('facebook.username', $form_state->getValue('facebook_username'))
      ->set('facebook.app_id', $form_state->getValue('facebook_app_id'))
      ->set('facebook.app_secret', $form_state->getValue('facebook_app_secret'))
      ->set('twitter.enabled', $form_state->getValue('twitter_enabled'))
      ->set('twitter.username', $form_state->getValue('twitter_username'))
      ->set('twitter.consumer_key', $form_state->getValue('twitter_consumer_key'))
      ->set('twitter.consumer_secret', $form_state->getValue('twitter_consumer_secret'))
      ->set('twitter.access_token', $form_state->getValue('twitter_access_token'))
      ->set('twitter.access_token_secret', $form_state->getValue('twitter_access_token_secret'))
      ->set('instagram.enabled', $form_state->getValue('instagram_enabled'))
      ->set('instagram.username', $form_state->getValue('instagram_username'))
      ->set('instagram.client_id', $form_state->getValue('instagram_client_id'))
      ->set('instagram.access_token', $form_state->getValue('instagram_access_token'))
      ->set('formats.post_format', $form_state->getValue('formats_post_format'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_feed_fetcher.settings'];
  }

}
