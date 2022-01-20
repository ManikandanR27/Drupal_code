<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class Myprofile {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while registering.';
  /**
   *The wsClient.
   *
   * @var Drupal\lp_lib\wsClient
   */
  protected $wsClient;
  /**
   * The UserSession.
   *
   * @var Drupal\lp_lib\Util\UserSession
   */
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
   * Get Panelist info by Panelist user name via Panelist-info endpoint.
   */
  public function panelistInfo(array $data) {

    // Calling Panelist Info API.
    $response = $this->wsClient->post('OneP-AccountServices/PanelistInfo', $data);
    return $response;
  }

  /**
   * Send (Patch) Panelist info to Panelist/{panelistId} endpoint.
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   */
  public function panelistInfoUpdate(array $data, $isAjax = FALSE) {
    // Calling Panelist Info API.
    if (isset($data['panelistId']) && !empty($data['panelistId'])) {
      $panelistId = $data['panelistId'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      // OP-6946 Endpoint ajax call to Panelist info update.
      if ($isAjax) {
        return $this->wsClient->profileUpdate('OneP-AccountServices/Panelist', $data, ['urlParams' => $panelistId], $isAjax);
      }
      return $this->wsClient->profileUpdate('OneP-AccountServices/Panelist', $data, ['urlParams' => $panelistId]);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Send (Patch) Panelist password update User endpoint.
   */
  public function panelistPasswordUpdate(array $data) {
    // Calling Panelist user password Update API.
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/ChangePassword', $data);
    return $response;
  }

  /**
   * Send (Patch) Panelist password update User endpoint ajax call.
   */
  public function ajaxPanelistPasswordUpdate(array $data) {
    // Calling Panelist user password Update API.
    $response = $this->wsClient->ajaxPost('OneP-AccountServices/Panelist/ChangePassword', $data);
    return $response;
  }

  /**
   * Send (Post) Panelist Change Account User endpoint.
   */
  public function panelistChangeAccount(array $data) {
    // Calling Panelist change account API.
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/ChangeAccount/Verification', $data);
    return $response;
  }

  /**
   * Process Profile and Password Update.
   */
  public function processProfileUpdate(array $data) {

  }

  /**
   * Get panelist New access Token details.
   */
  public function panelistTokenDetails(array $data, array $panelistData) {
    $response = $this->wsClient->get('OneP-SecurityServices/User', $panelistData, $data);
    return $response;
  }

  /**
   * Send (Post) Panelist Change Account User endpoint ajax call.
   */
  public function ajaxPanelistChangeAccount(array $data) {
    // Calling Panelist change account API - OP-4166
    $response = $this->wsClient->ajaxPost('OneP-AccountServices/Panelist/ChangeAccount/Verification', $data);
    return $response;
  }

}
