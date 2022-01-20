<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class Unsubscribe {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while unsubscribe.';

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
   * Register a callback for DOI redirection.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function closeAccount(array $data) {
    $response = $this->wsClient->delete('OneP-AccountServices/Panelist', $data);
    return $response;
  }

  /**
   * Unsubscribe from Email.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function unsubscribeByEmail(array $data) {
    // Retrive panelist id from data to append in endpoint.
    $panelistId = $data['id'];
    // Unset the id as this is not a part of data.
    unset($data['id']);
    $data['from'] = 'emailActivation';
    $response = $this->wsClient->postJson('OneP-Bridge/ACM/UnsubscribeFromEmail', $data);
    return $response;
  }

}
