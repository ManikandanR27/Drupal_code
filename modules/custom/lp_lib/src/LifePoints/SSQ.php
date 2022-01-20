<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WsClient object of common services for SSQ.
 */
class SSQ {

  protected $wsClient;

  /**
   * Construct for getting surveys to login panelist.
   */
  public function __construct(WsClientInterface $wsClient) {
    $this->wsClient = $wsClient;
  }

  /**
   * POST request to saveSSQOffer.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function saveSSQOffer(array $data) {
    if (isset($data['panelistID']) && !empty($data['panelistID'])) {
      $panelistId[] = $data['panelistID'];
      unset($data['panelistID']);
      $response = $this->wsClient->postJson('SaveSSQOffer', $data, $panelistId);
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * POST request to saveSSQResponse.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function saveSSQResponse(array $data) {
    if (isset($data['panelistID']) && !empty($data['panelistID'])) {
      $panelistId[] = $data['panelistID'];
      unset($data['panelistID']);
      $response = $this->wsClient->postJson('SaveSSQResponse', $data, $panelistId);
      return $response;
    }
    else {
      return FALSE;
    }
  }
}
