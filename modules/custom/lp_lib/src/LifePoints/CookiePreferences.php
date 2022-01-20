<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class CookiePreferences {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while cookie fetching.';

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
   * Cookie Preferences.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function getCookiesForPanelist(array $panelistData) {
    // Calling Panelist Info.
    if (isset($panelistData['panelistId']) && !empty($panelistData['panelistId'])) {
      $panelistId = $panelistData['panelistId'];
      // Sending it on url so need to remove from data.
      unset($panelistData['panelistId']);

      $response = $this->wsClient->cookiePortalGet('OneP-Cookie/ConsentCookie/PanelistId', $panelistData, ['urlParams' => $panelistId]);
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Cookie Portal Post Endpoint --
   * JsonData : {
   * "cookies": [
   * {
   * "cookieId": "27265c42-7182-4d08-83ff-8a865fec135f",
   * "status": 2,
   * "location": "some portal page url",
   * "consentTS": "2018/07/05 18:39:27Z"
   * }
   * ]
   * }.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function replaceCookiesForPanelist(array $panelistData) {
    // Calling Panelist Info.
    if (isset($panelistData['panelistId']) && !empty($panelistData['panelistId'])) {
      $panelistId = $panelistData['panelistId'];
      // Sending it on url so need to remove from data.
      unset($panelistData['panelistId']);
      $response = $this->wsClient->postJson('OneP-Cookie/ConsentCookie/PanelistId', $panelistData, ['urlParams' => $panelistId]);
      return $response;
    }
    else {
      return FALSE;
    }
  }

}
