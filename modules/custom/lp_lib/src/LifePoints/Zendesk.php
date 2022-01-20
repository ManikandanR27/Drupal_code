<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class Zendesk {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while zendesk.';

  protected $wsClient;
  protected $userSession;
  protected $path;

  /**
   * Cunstructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }

  /**
   * Zendesk Help Center url.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function zendeskHelpCenterUrl(array $data, array $panelistData) {
    $response = $this->wsClient->get('OneP-Bridge/ZendeskUrl/HelpCenter/PanelistId', [], $panelistData);
    return $response;
  }

  /**
   * Zendesk Redeem url.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function zendeskRedeemUrl(array $data, array $panelistData) {
    $response = $this->wsClient->postJson('OneP-Bridge/ZendeskUrl/Redeem/PanelistId', $data, $panelistData);
    return $response;
  }

  /**
   * Zendesk SurveyIncentive url.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function zendeskSurveyIncentiveUrl(array $data, array $panelistData) {
    $response = $this->wsClient->postJson('OneP-Bridge/ZendeskUrl/SurveyIncentive/PanelistId', $data, $panelistData);
    return $response;
  }

}
