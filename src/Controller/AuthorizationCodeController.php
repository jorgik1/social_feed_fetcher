<?php

namespace Drupal\social_feed_fetcher\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use LinkedIn\AccessToken;
use LinkedIn\Client;
use LinkedIn\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * AuthorizationCodeController class.
 */
class AuthorizationCodeController extends ControllerBase {

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
   * LoggerInterface definition.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The client of linkedin.
   *
   * @var \LinkedIn\Client
   */
  public $client;

  /**
   * AuthorizationCodeController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   MessengerInterface definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   LoggerInterface definition.
   * @param \LinkedIn\Client $client
   *   The linkedin client.
   */
  public function __construct(MessengerInterface $messenger, RequestStack $requestStack, ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $loggerChannelFactory, Client $client) {
    $this->messenger = $messenger;
    $this->requestStack = $requestStack;
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('social_feed_fetcher');
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('social_feed_fetcher.linkedin.client')
    );
  }

  /**
   * Catch response from Linkedin authentication to get an authorization code.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  public function getResponse(Request $request) {
    $process = FALSE;
    if ($request->query->has('code')) {
      $code = $request->query->get('code');
      $process = $this->getAccessToken($code);
    }
    if ($request->query->has('error')) {
      $message = $request->query->get('error_description');
      $formatted_message = $this->t('Social feed fetcher error: @error', ['@error' => $message]);
      $this->logger->error($formatted_message);
      $this->messenger->addError($formatted_message);
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
   * @return bool|\LinkedIn\AccessToken
   *   The result of request.
   */
  protected function getAccessToken($code) {
    $redirect_url = $this->requestStack->getCurrentRequest()->getHost();
    $scheme = $this->requestStack->getCurrentRequest()->getScheme();
    $this->client->setRedirectUrl(sprintf('%s://%s/oauth/callback', $scheme, $redirect_url));
    try {
      $token = $this->client->getAccessToken($code);
    }
    catch (Exception $exception) {
      $this->logger->error('Social feed fetcher error: ' . $exception->getMessage());
      return FALSE;
    }
    $this->setAccessToken($token);
    return $token;
  }

  /**
   * Set as variable the value of access token and expires_in.
   *
   * @param \LinkedIn\AccessToken $accessToken
   *   The access token.
   *
   * @return bool
   *   Set to true if token exist.
   */
  protected function setAccessToken(AccessToken $accessToken) {
    if ($accessToken !== NULL) {
      $this->state()->set('access_token', $accessToken->getToken());
      $this->state()->set('expires_in', $accessToken->getExpiresIn());
      $this->state()->set('expires_in_save', time());
      return TRUE;
    }
    return FALSE;
  }

}
