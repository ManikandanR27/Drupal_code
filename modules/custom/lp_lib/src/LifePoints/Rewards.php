<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class Rewards {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while Rewards.';

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
   * Rewards Redeem url.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function rewardsRedeemUrl(array $data, array $panelistData) {
    $response = $this->wsClient->get('OneP-IncentiveServices/PerksLogin', $data, $panelistData);
    return $response;
  }

}
