<?php

namespace Drupal\social_feed_fetcher\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\CronInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
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
   * Request time value.
   *
   * @var int
   */
  private $requestTime;

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
    $this->requestTime = \Drupal::time()->getRequestTime();

    $next_execution = $this->state->get('social_feed_fetcher.next_execution');
    $next_execution = !empty($next_execution) ? $next_execution : $this->requestTime;

    $args = [
      '%time' => date_iso8601($this->state->get('social_feed_fetcher.next_execution')),
      '%seconds' => $next_execution - $this->requestTime,
    ];
    $form['status']['last'] = [
      '#type' => 'item',
      '#markup' => $this->t('The Social Feed Fetcher will next execute the first time the cron runs after %time (%seconds seconds from now)', $args),
    ];

    $form['facebook'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook settings'),
      '#open' => TRUE,
    ];

    $form['facebook']['facebook_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#default_value' => $config->get('facebook.enabled'),
    ];

    $form['facebook']['fb_page_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Page Name'),
      '#default_value' => $config->get('fb.page_name'),
      '#description' => $this->t('eg. If your Facebook page URL is this http://www.facebook.com/YOUR_PAGE_NAME, <br />then you just need to add this YOUR_PAGE_NAME above.'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['facebook']['fb_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App ID'),
      '#default_value' => $config->get('fb.app_id'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['facebook']['fb_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Secret Key'),
      '#default_value' => $config->get('fb.secret_key'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['facebook']['fb_no_feeds'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Feeds'),
      '#default_value' => $config->get('fb.no_feeds'),
      '#size' => 60,
      '#maxlength' => 60,
      '#max' => 100,
      '#min' => 1,
    ];


    $form['twitter'] = [
      '#type' => 'details',
      '#title' => $this->t('Twitter settings'),
      '#open' => TRUE,
    ];

    $form['twitter']['twitter_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#default_value' => $config->get('twitter.enabled'),
    ];

    $form['twitter']['tw_consumer_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Twitter Consumer Key'),
      '#default_value' => $config->get('tw.consumer_key'),
      '#size'          => 60,
      '#maxlength'     => 100,
      '#required'      => TRUE,
    ];
    $form['twitter']['tw_consumer_secret'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Twitter Consumer Secret'),
      '#default_value' => $config->get('tw.consumer_secret'),
      '#size'          => 60,
      '#maxlength'     => 100,
      '#required'      => TRUE,
    ];
    $form['twitter']['tw_access_token'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Twitter Access Token'),
      '#default_value' => $config->get('tw.access_token'),
      '#size'          => 60,
      '#maxlength'     => 100,
      '#required'      => TRUE,
    ];
    $form['twitter']['tw_access_token_secret'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Twitter Access Token Secret'),
      '#default_value' => $config->get('tw.access_token_secret'),
      '#size'          => 60,
      '#maxlength'     => 100,
      '#required'      => TRUE,
    ];
    $form['twitter']['tw_count'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Tweets Count'),
      '#default_value' => $config->get('tw.count'),
      '#size'          => 60,
      '#maxlength'     => 100,
      '#min'           => 1,
    ];

    $form['instagram'] = [
      '#type' => 'details',
      '#title' => $this->t('Instagram settings'),
      '#open' => TRUE,
    ];

    $form['instagram']['instagram_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#default_value' => $config->get('instagram.enabled'),
    ];
    $form['instagram']['header']['#markup'] = $this->t('To get Client ID you need to manage clients from your instagram account detailed information <a href="@admin" target="@blank">here</a>.', [
      '@admin' => Url::fromRoute('help.page',
        ['name' => 'socialfeed'])->toString(),
      '@blank' => '_blank',
    ]);
    $form['instagram']['in_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('Client ID from Instagram account'),
      '#default_value' => $config->get('in.client_id'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['instagram']['in_redirect_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URI'),
      '#description' => $this->t('Redirect URI from Instagram account'),
      '#default_value' => $config->get('in.redirect_uri'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['instagram']['in_auth_link'] = [
      '#type' => 'item',
      '#title' => $this->t('Generate Instagram Access Token'),
      '#description' => $this->t('Access this URL in your browser https://instagram.com/oauth/authorize/?client_id=&lt;your_client_id&gt;&redirect_uri=&lt;your_redirect_uri&gt;&response_type=token, you will get the access token.'),
      '#default_value' => $config->get('in.auth_link'),
      '#markup' => $this->t('Check <a href="@this" target="_blank">this</a> article.', [
        '@this' => Url::fromUri('http://jelled.com/instagram/access-token')
          ->toString(),
      ]),
    ];
    $form['instagram']['in_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#default_value' => $config->get('in.access_token'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['instagram']['in_picture_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Picture Count'),
      '#default_value' => $config->get('in.picture_count'),
      '#size' => 60,
      '#maxlength' => 100,
      '#min' => 1,
    ];
    if ($config->get('in.access_token')) {
      $form['instagram']['feed'] = [
        '#type' => 'item',
        '#title' => $this->t('Feed URL'),
        '#markup' => $this->t('https://api.instagram.com/v1/users/self/feed?access_token=@access_token&count=@picture_count',
          [
            '@access_token' => $config->get('in.access_token'),
            '@picture_count' => $config->get('in.picture_count'),
          ]
        ),
      ];
    }
    $form['instagram']['in_picture_resolution'] = [
      '#type' => 'select',
      '#title' => $this->t('Picture Resolution'),
      '#default_value' => $config->get('in.picture_resolution'),
      '#options' => [
        'thumbnail' => $this->t('Thumbnail'),
        'low_resolution' => $this->t('Low Resolution'),
        'standard_resolution' => $this->t('Standard Resolution'),
      ],
    ];
    $form['instagram']['in_post_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show post URL'),
      '#default_value' => $config->get('in.post_link'),
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
      '#options' => $allowed_formats,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
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
      ->set('fb.page_name', $form_state->getValue('fb_page_name'))
      ->set('fb.app_id', $form_state->getValue('fb_app_id'))
      ->set('fb.secret_key', $form_state->getValue('fb_secret_key'))
      ->set('fb.no_feeds', $form_state->getValue('fb_no_feeds'))
      ->set('twitter.enabled', $form_state->getValue('twitter_enabled'))
      ->set('tw.consumer_key', $form_state->getValue('tw_consumer_key'))
      ->set('tw.consumer_secret', $form_state->getValue('tw_consumer_secret'))
      ->set('tw.access_token', $form_state->getValue('tw_access_token'))
      ->set('tw.access_token_secret', $form_state->getValue('tw_access_token_secret'))
      ->set('tw.count', $form_state->getValue('tw_count'))
      ->set('instagram.enabled', $form_state->getValue('instagram_enabled'))
      ->set('in.client_id', $form_state->getValue('in_client_id'))
      ->set('in.redirect_uri', $form_state->getValue('in_redirect_uri'))
      ->set('in.auth_link', $form_state->getValue('in_auth_link'))
      ->set('in.access_token', $form_state->getValue('in_access_token'))
      ->set('in.picture_count', $form_state->getValue('in_picture_count'))
      ->set('in.picture_resolution', $form_state->getValue('in_picture_resolution'))
      ->set('in.post_link', $form_state->getValue('in_post_link'))
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
