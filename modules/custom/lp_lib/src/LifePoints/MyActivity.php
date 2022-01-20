<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class MyActivity {

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
   * Get Panelist Returns all point transactions for a panelist via Transaction endpoint.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getMyActivityTransactions(array $data) {
    // Calling Panelist Info API.
    if (isset($data['panelistId']) && !empty($data['panelistId'])) {
      $panelistId = $data['panelistId'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      $response = $this->wsClient->get('OneP-IncentiveServices/Transactions', $data, ['urlParams' => $panelistId]);

      return $response;
    }
    else {
      return FALSE;
    }

  }

}
