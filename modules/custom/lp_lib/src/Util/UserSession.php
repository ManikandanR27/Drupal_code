<?php

namespace Drupal\lp_lib\Util;

use Drupal\lp_lib\Session\SessionManager;
use Drupal\lp_lib\WsClient\CommonServiceResponse;
use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\lp_lib\LifePoints\Myprofile;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * This class for maintaining user session data.
 */
class UserSession {

  protected $settingsConfig;
  protected $session_manager;
  private $mappingUsage;

  /**
   * Cunstroctor to initialize session manager.
   */
  public function __construct($sessionManager, $config, $request) {
    $this->session_manager = $sessionManager;
    $settingsConfig['OneP_SecurityServices_Host'] = $config->get('lp')['common_service']['OneP-SecurityServices-Host'];
    $settingsConfig['Token_Authorization'] = $config->get('Token_Authorization');
    $settingsConfig['Session_Timeout'] = $config->get('Session_Timeout');
    $this->settingsConfig = $settingsConfig;
    $this->request = $request->getCurrentRequest();
  }

  /**
   * Function to initiate panelist login.
   */
  public function panelistLogin() {
    // OP-6867: Lifepoints: Clear PHP Warning and Notices.
    // Notice: session_start(); An session had already been started.
    // session_start();
    $this->session_manager->startSession();
    $this->session_manager->set("panelist", []);
    $this->addPanelistSessionData("LastTokenActivityTime", $_SERVER['REQUEST_TIME']);
    $this->addPanelistSessionData("LastActivity", $_SERVER['REQUEST_TIME']);
  }

  /**
   * Function to initiate panelist logout.
   * OP-5605 - Added default param redirectRoute for redirect user on specific page
   */
  public function panelistLogout($errorMsg = NULL, $noRedirect = FALSE, $redirectRoute = NULL) {
    $this->session_manager->destroySession();
    // OP-5161 - To refresh the session.
    // Migrate the current session from anonymous to authenticated (or vice-versa).
    $this->request->getSession()->migrate();
    if ($errorMsg != NULL) {
      // OP-6796 OP-6588.
      // Remove session time out message from mobile app.
      $refererUrl = $this->request->server->get('HTTP_REFERER');
      $parsedUrl = UrlHelper::parse($refererUrl);
      $queryParams = $parsedUrl['query'];
      if (!isset($queryParams['device']) && $queryParams['device'] != 'app') {
        \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping($errorMsg));
      }
    }

    // OP-5605 - Redirected panelist default to Homepage.
    // If redirectRoute is defined like login or unsubscribeConfirm then redirected to specific page.
    if ($noRedirect == FALSE) {
      if ($redirectRoute == NULL) {
        $redirectRoute = '<front>';
      }
      $redirectResponse = \Drupal::service('redirect_response');
      // Call to Recreate URL when Default EN lang come in URL or as default Panel.
      $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel($redirectRoute));
      $redirectResponse->send();
      return [
        '#cache' => ['max-age' => 0],
      ];
    }
  }

  /**
   * Function to check panelist logged in or not.
   */
  public function isPanelistLoggedIn() {
    return is_array($this->getPanelistSessionData());
  }

  /**
   * Function to check Admin user.
   */
  public function isAdminUser() {
    $user = \Drupal::currentUser()->getRoles();
    return in_array("administrator", $user);
  }

  /**
   * Function to check authenticated user.
   */
  public function isAuthenticated() {
    return $this->isPanelistLoggedIn() || $this->isAdminUser();
  }

  /**
   * Function to add response data in session.
   */
  public function addPanelistSessionData($k, $v) {

    if (!$this->isPanelistLoggedIn()) {
      return;
    }

    $panelist_session = $this->session_manager->get("panelist");
    $panelist_session[$k] = $v;
    $this->session_manager->set("panelist", $panelist_session);
  }

  /**
   * Function to set panalist data.
   */
  public function setPanelistSessionData($v) {
    $this->session_manager->set("panelist", $v);
  }

  /**
   * Function to get panelist session data.
   */
  public function getPanelistSessionData() {
    return $this->session_manager->get("panelist");
  }

  /**
   * Function to get regenerate token data.
   */
  public function getToken() {

    $getPanelistSessionData = $this->getPanelistSessionData();
    $lastTokenActivityTime = $getPanelistSessionData['LastTokenActivityTime'];
    $currentTime = $_SERVER['REQUEST_TIME'];
    $timeOut = $this->settingsConfig['Session_Timeout'];
    if (isset($lastTokenActivityTime) && ($currentTime - $lastTokenActivityTime >= $timeOut)) {
      $paramsData = [];
      $userData = [];
      $userData['userName'] = $getPanelistSessionData['emailAddress'];
      // Calling Panelist get new token API.
      $myprofile = \Drupal::service('lp.myprofile');
      $responseData = $myprofile->panelistTokenDetails($paramsData, $userData);
      $responseData = $responseData->data();
      $tokenResponse = $responseData['tokenInfo'];
      if (isset($tokenResponse) && !empty($tokenResponse) && $tokenResponse['access_token']) {
        // Updating session with new token details.
        $this->addPanelistSessionData('accessToken', $tokenResponse['access_token']);
        $this->addPanelistSessionData('refreshToken', $tokenResponse['refresh_token']);
        $this->addPanelistSessionData('expiresIn', $tokenResponse['expires_in']);
        $this->addPanelistSessionData("LastTokenActivityTime", $_SERVER['REQUEST_TIME']);
        return $tokenResponse['access_token'];
      }
      else {
        // Token expired then logout.
        // OP-6043 - Token expired log entry.
        \Drupal::logger('lp_behavior')->info(
          $getPanelistSessionData['emailAddress'] . ' session time out because of token expired ');
        // OP-5605 - Redirect user using common panelistLogout function.
        $this->panelistLogout('token_expired', FALSE, 'lp_login.Login');
        /*$url = Url::fromRoute('lp_login.Login');
        $redirectResponse = \Drupal::service('redirect_response');
        $redirectResponse->setTargetUrl($url->toString());
        $redirectResponse->send();*/
      }
    }
    else {
      return $getPanelistSessionData['accessToken'];
    }
  }

  /**
   * Initiate Panelist Session Data.
   */
  public function initiatePanelistSessionData($panelistData = [], $tokenInfo = []) {

    $panelistKeys = ['panelistId', 'firstName', 'lastName', 'memberId', 'emailAddress', 'status', 'gender', 'dateOfBirth', 'pointsEarned', 'postalCode', 'panelistDetails', 'rewards', 'panelistAttributes'];
    $panelistTokens = ['accessToken', 'refreshToken', 'expiresIn'];
    $panelistDetailsData = ['address1', 'address2', 'city', 'cityCode', 'cityEnglish', 'cityNative', 'state', 'stateCode', 'stateEnglish', 'stateNative', 'country', 'language', 'createDate', 'reg1', 'terms', 'termsVersion', 'psReason', 'verityScore', 'verityChallengeScore', 'pointsEarnedReg1', 'pointsEarnedReg2', 'SSQTrackingID', 'isSaveSSQOfferAPILogged'];
    // OP-4249.
    // Data coming from panelist Attributes section. Custom variables.
    $panelistAttributesData = ['isDemoPopupCompleted','productTour'];
    $panelist = $panelistData;
    $tokenData = $tokenInfo;

    // Panelist.
    if (isset($panelist) && !empty($panelist)) {
      foreach ($panelist as $key => $value) {
        if (in_array($key, $panelistKeys)) {
          if ($key == 'panelistDetails') {
            foreach ($panelistDetailsData as $panelistDetailsKey) {
              if (isset($value[$panelistDetailsKey])) {
                $this->addPanelistSessionData($panelistDetailsKey, $value[$panelistDetailsKey]);
              }
            }
          }
          // OP-4249.
          // Check for data parameters in Panelist Attributes.
          elseif ($key == 'panelistAttributes') {
            foreach ($panelistAttributesData as $panelistAttrDetailsKey) {
              // For new users.
              if (isset($value[$panelistAttrDetailsKey])) {
                $this->addPanelistSessionData($panelistAttrDetailsKey, $value[$panelistAttrDetailsKey]);
              }
              // Fors existing user the value is not predefined. Setting to FALSE (zero).
              else {
                $this->addPanelistSessionData($panelistAttrDetailsKey, 0);
              }
            }
          }
          else {
            $this->addPanelistSessionData($key, $value);
          }
        }
      }
    }

    // tokenInfo.
    if (isset($tokenData) && !empty($tokenData)) {
      foreach ($panelistTokens as $tokenKey) {
        $this->addPanelistSessionData($tokenKey, $tokenData[$tokenKey]);
      }
    }
    // Adding webcam status to panlist session.
    $externalSession = $this->request->getSession();
    $panelistExternalSessions = $externalSession->get('panelistExternalSessions');

    if (isset($panelistExternalSessions) && !empty($panelistExternalSessions)) {
      $this->addPanelistSessionData("panelistSystemWebCamStatus", $panelistExternalSessions['panelistSystemWebCamStatus']);
    }

    // OP-3413 : Store Maxmind API response in session after Reset Password auto login in Portal
    // Adding Panel to session after login so we can validate the panel change detection during session.
    $this->addPanelistSessionData('panelistActivePanel', MappingUsages::get_panel_code());

    // Get Country code by IP to store it in session.
    $countryCode = MappingUsages::getCountryIPandCode()['country_code'];
    // Add country code in session.
    $this->addPanelistSessionData('panelistCountryCode', $countryCode);
    // Add ip address in session.
    $this->addPanelistSessionData('panelistIp', MappingUsages::get_client_ip_address());
    // OP-5161 - To get the current sessionId.
    $this->addPanelistSessionData('lsid', $this->getLsid());

    return;
  }

  /**
   * Function to return LSID (Session ID).
   * OP-5463 LSID missing from session.
   *
   * @return [mixed] sessionId
   */
  public function getLsid($regenerate = FALSE) {
    if (isset($this->getPanelistSessionData()['lsid'])) {
      return $this->getPanelistSessionData()['lsid'];
    }
    elseif($regenerate) {
      \Drupal::service('session_manager')->regenerate();
      return $this->request->getSession()->getId();
    }
    else {
      // Initiate the same session for non logged in users.
      // Fix for mis-match session id issue for nudetect init call and login endpoint.
      $this->session_manager->startSession();
      return $this->request->getSession()->getId();
    }
  }

  /**
   * Function to set the alternate acceptLanguage and alternatePanelCode.
   * OP-6565 - To pass alternativePanelistPanelCode into session.
   */
  public function setAlternateAcceptLanguage($alternateAcceptLanguage) {
    if ($this->isPanelistLoggedIn()) {
      $panelistActivePanel = explode('-', $alternateAcceptLanguage);
      $panelCode = strtoupper($panelistActivePanel[1] . '_' . $panelistActivePanel[0]);

      $this->addPanelistSessionData('loggedInAcceptLanguage', strtolower($alternateAcceptLanguage));
      $this->addPanelistSessionData('alternativePanelistPanelCode', $panelCode);
      $this->addPanelistSessionData('panelistActivePanel', $panelCode);
    }
  }
}
