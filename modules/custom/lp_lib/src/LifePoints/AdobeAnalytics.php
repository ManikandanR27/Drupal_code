<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class AdobeAnalytics {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred.';

  protected $wsClient;
  protected $userSession;
  protected $path;

  /**
   * Constructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }

  /**
   * Send Adobe Analytics to the Adobe server.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function sendAdobeAnalytics($adobeUrl, array $data) {

    // To send adobe analytics.
    if (isset($data['ip']) && !empty($data['ip'])) {
      $response = $this->wsClient->adobeAnalyticsGet($adobeUrl, $data);
      return $response;
    }
    else {
      return FALSE;
    }
  }

}
