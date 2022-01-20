<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;
use Drupal\Core\Url;

/**
 * WS response object for common service for My Surveys.
 */
class EventReward {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while retrieving studies';

  protected $wsClient;
  protected $userSession;

  /**
   * Construct for getting surveys to login panelist.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }


  /**
   * reg1 reg2 Portal GET request.
   *
   * Combined services for reg1 and reg2 points.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getEventRewardPoints(array $data) {
    $response = $this->wsClient->get('OneP-RecruitmentServices/EventReward', $data);
    return $response;
  }

  /**
   * GET request for reg1/reg2 points.
   *
   * Combined services for reg1 and reg2 points.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getEventRewardPointsWithoutPanelist(array $data) {
     if (isset($data['event']) && !empty($data['event'])) {
      $event[] = $data['event'];
      // Sending it on url so need to remove from data.
      unset($data['event']);
      $response = $this->wsClient->get('OneP-RecruitmentServices/EventReward', $data, $event);
      return $response;
    }
    else {
      return FALSE;
    }
  }
}
