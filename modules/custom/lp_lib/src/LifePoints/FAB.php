<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class FAB {

  protected $wsClient;

  /**
   * Constructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient) {
    $this->wsClient = $wsClient;
  }

  /**
   * POST- FAB Feedback Form.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function postFloatingButtonFeedback(array $data) {
    if (isset($data['domain']) && !empty($data['domain'])) {
      $domain = $data['domain'];
      // Unset domain from the data array.
      unset($data['domain']);
      $response = $this->wsClient->postJson('OneP-Bridge/Feedback/FloatingButton/' . $domain, $data);
      return $response;
    }
    else {
      return FALSE;
    }
  }

}
