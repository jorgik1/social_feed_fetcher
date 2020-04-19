<?php

namespace Drupal\social_feed_fetcher\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AuthorizationInstagramController class.
 */
class AuthorizationInstagramController extends ControllerBase {

  /**
   * MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The instagram client.
   *
   * @var \EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay
   */
  protected $instagramClient;

  /**
   * AuthorizationCodeController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   MessengerInterface definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface definition.
   * @param \EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay $instagramBasicDisplay
   *   The instagram client.
   */
  public function __construct(MessengerInterface $messenger, RequestStack $requestStack, ConfigFactoryInterface $configFactory, InstagramBasicDisplay $instagramBasicDisplay) {
    $this->messenger = $messenger;
    $this->requestStack = $requestStack;
    $this->configFactory = $configFactory;
    $this->instagramClient = $instagramBasicDisplay;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('social_feed_fetcher.instagram.client')
    );
  }

  /**
   * Catch response from Linkedin authentication to get an authorization code.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The symfony request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResponse(Request $request) {
    $process = FALSE;
    if ($request->query->has('code')) {
      $code = $request->query->get('code');
      $process = $this->getAccessToken($code);
    }

    $url = Url::fromRoute('social_feed_fetcher.settings');
    if ($process) {
      $this->messenger->addMessage($this->t('Register success'), $this->messenger::TYPE_STATUS);
      return new RedirectResponse($url->toString());
    }
    $this->messenger->addMessage($this->t('Register non success'), $this->messenger::TYPE_ERROR);
    return new RedirectResponse($url->toString());
  }

  /**
   * Call to linkedin api to get an access token and expires_in value.
   *
   * @param string $code
   *   The authorization token.
   *
   * @return bool
   *   The result of request.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function getAccessToken($code) {
    $client = new Client([
      'base_uri' => 'https://api.instagram.com',
      'allow_redirects' => TRUE,
      'timeout' => 0,
    ]);

    $redirect_url = $this->requestStack->getCurrentRequest()->getHost();
    $config = $this->configFactory->getEditable('social_feed_fetcher.settings');

    $response = $client->request(
      'POST',
      '/oauth/access_token',
      [
        'headers' => [
          'Content-Type' => "application/x-www-form-urlencoded",
        ],
        'form_params' => [
          'client_id' => $config->get('in_client_id'),
          'client_secret' => $config->get('in_client_secret'),
          'code' => $code,
          'grant_type' => 'authorization_code',
          'redirect_uri' => 'https://' . $redirect_url . '/instagram/oauth/callback',
        ],
      ]
    );

    if (isset($response)) {
      $data = $response->getBody()->getContents();
      $content = Json::decode($data);
      return $this->setAccessToken($content);
    }

    return FALSE;
  }

  /**
   * Set as variable the value of access token and expires_in.
   *
   * @param string $content
   *   The access token and the expires_in value.
   *
   * @return bool
   *   Set to true.
   */
  protected function setAccessToken($content) {
    if (isset($content['access_token'])) {
      $longLivedData = $this->instagramClient->getLongLivedToken($content['access_token']);
      $this->state()->set('insta_access_token', $longLivedData->access_token);
      $this->state()->set('insta_expires_in', $longLivedData->expires_in);
      $this->state()->set('insta_expires_in_save', time());
      return TRUE;
    }
    return FALSE;
  }

}
