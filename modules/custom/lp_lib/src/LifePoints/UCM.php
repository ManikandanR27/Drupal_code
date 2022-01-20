<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 *
 * LifePoints portal Terms & Conditions text and translation from OneTrust and
 * display it to the portal registration form. On answer, flow data to OneTrust.
 */
class UCM {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while logging in. Please try again';

  protected $wsClient;
  protected $userSession;
  protected $request;

  /**
   * Constructor to setting up required variables.
   */
  public function __construct(WsClientInterface $wsClient, $userSession, $request) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
    $this->request     = $request->getCurrentRequest();
  }

  /**
   * Calls to UCM - One Trust ConsentGroup
   * Get Retrieve list of supported consent groups for your account.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getUCMConsentGroup(array $data) {
    // Calling consent details from One Trust API.
    if (isset($data['group']) && !empty($data['group'])) {
      $consentGroup = $data['group'];
      // Sending it on url so need to remove from data.
      unset($data['group']);

      // Calling consent details from One Trust API.
      $response = $this->wsClient->get(
        'KantarConsent/ConsentGroup/' . $consentGroup,
        $data,
        ['urlParams' => NULL]
      );
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Calls to UCM - One Trust Consent Status.
   * Retrieve panelist consents status for a specific group.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getUCMPanelistConsentsStatus(array $data) {
    // Calling GET consent status from One Trust API.
    if (
      isset($data['panelistId']) && !empty($data['panelistId']) &&
      isset($data['group']) && !empty($data['group'])
    ) {

      $panelistId = $data['panelistId'];
      // UCM Consent group.
      $consentGroup = $data['group'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      unset($data['group']);

      $response = $this->wsClient->get('KantarConsent/ConsentGroup/' . $consentGroup .
        '/Panelist/' . $panelistId .
        '/Status', $data, ['urlParams' => NULL]);
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Calls to UCM - One Trust Consent Status.
   * Retrieve panelist consent required for a specific group.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getUCMPanelistConsentsRequired(array $data) {
    // Calling GET consents required from One Trust API.
    if (
      isset($data['panelistId']) && !empty($data['panelistId']) &&
      isset($data['group']) && !empty($data['group'])
    ) {

      $panelistId = $data['panelistId'];
      // UCM Consent group.
      $consentGroup = $data['group'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      unset($data['group']);

      $response = $this->wsClient->get('KantarConsent/ConsentGroup/' . $consentGroup .
        '/Panelist/' . $panelistId .
        '/Required', $data, ['urlParams' => NULL]);
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Calls to UCM - One Trust ConsentGroupTranslations
   * Get Retrieve list of supported consent groups for your account.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getUCMConsentGroupTranslations(array $data) {
    // Calling consent details from One Trust API.
    if (isset($data['group']) && !empty($data['group'])) {
      $consentGroup = $data['group'];
      $version = $data['currentVersion'];
      $locale = $data['locale'];
      // Sending it on url so need to remove from data.
      unset($data['group']);
      unset($data['currentVersion']);

      // Calling consent details from One Trust API.
      $response = $this->wsClient->get(
        'KantarConsent/ConsentGroup/' . $consentGroup . '/Translations/Version/' . $version . '/Locale/' . $locale,
        $data,
        ['urlParams' => NULL]
      );
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Calls to UCM - One Trust ConsentGroup
   * Update panelist consents status for the consent group.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function postUCMUpdatePanelistConsentsStatus(array $data) {

    // Calling Update Panelist Consents Status from One Trust API.
    if (
      isset($data['panelistId']) && !empty($data['panelistId']) &&
      isset($data['group']) && !empty($data['group'])
    ) {

      $panelistId = $data['panelistId'];
      // UCM Consent group.
      $consentGroup = $data['group'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      unset($data['group']);
      $response = $this->wsClient->postJson('KantarConsent/ConsentGroup/' . $consentGroup .
        '/Panelist/' . $panelistId . '/Status', $data, ['urlParams' => NULL]);
      return $response;
    }
    else {
      return FALSE;
    }
  }


  /**
   * Calls to UCM - One Trust ConsentGroup
   * Update panelist consents status for the consent group.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function grantConsentInviteForPanelist(array $data) {

    // Calling Update Panelist Consents Status from One Trust API.
    if (
      isset($data['panelistId']) && !empty($data['panelistId']) &&
      isset($data['group']) && !empty($data['group']) &&
      isset($data['partner']) && !empty($data['partner'])
    ) {
      $partner = $data['partner'];
      $panelistId = $data['panelistId'];
      // UCM Consent group.
      $consentGroup = $data['group'];
      // Sending it on url so need to remove from data.
      unset($data['panelistId']);
      unset($data['group']);
      unset($data['partner']);
      $response = $this->wsClient->postJson('KantarConsent/ConsentInvite/'. $partner .'/ConsentGroup/'. $consentGroup .'/Panelist/'. $panelistId .'/Grant', $data, ['urlParams' => NULL]);
      return $response;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Function to get ucm cache.
   */
  public function getUCMTranslationByCache(array $data) {
    // Get current accept language.
    $panelConfig = \Drupal::service('lp.configuration');
    $panelAcceptLang = $panelConfig->getAcceptLanguage();

    // To set the variable in cache.
    $cid = "lp_cache_ucm_version_" . $panelAcceptLang;

    if ($cache = \Drupal::cache()->get($cid)) {
      $cache_ucm_data = $cache->data;

      // To check the version if there is different update the cache.
      if (($data['currentVersion']) > ($cache_ucm_data->data()['CurrentVersion'])) {
        // To call UCM Translations.
        $getUCMResponse = $this->getUCMConsentGroupTranslations($data);

        if (!empty($getUCMResponse)) {
          $cache_ucm_data = $getUCMResponse;
          // Set the cache.
          \Drupal::cache()->set($cid, $cache_ucm_data);
        }
      }
      else {
        // Nothing to do.
        // Consent version machetd Response coming from drrupal cache.
      }
    }
    else {
      // To call UCM Translations.
      $getUCMResponse = $this->getUCMConsentGroupTranslations($data);
      if (!empty($getUCMResponse)) {
        $cache_ucm_data = $getUCMResponse;
        // Set the cache.
        \Drupal::cache()->set($cid, $cache_ucm_data);
      }
    }
    return $cache_ucm_data;
  }

  /**
   * Calls to UCM - One Trust Consent Status.
   * Retrieve the panelist consent status for given panelist.
   * Return Value: UNKNOWN, CONSENTED, NOT_CONSENTED.
   *
   * Example URL format
   * https://onep.api.developer.platone.red/KantarConsent/
   * ConsentInvite/{partner}/ConsentGroup/{group}/Panelist/{panelistId}/Status
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getConsentInviteStatusByPanelist(array $data) {

    // Calling GET consent status from One Trust API.
    if (
      isset($data['panelistId']) && !empty($data['panelistId']) &&
      isset($data['group']) && !empty($data['group'])
    ) {

      $panelistId = $data['panelistId'];
      // UCM Consent group name.
      $consentGroup = $data['group'];
      // Partners require consent invite, currently only Google supported. Example "Google".
      $partner = $data['partner'];

      // Removing values from data as it not required in API data.
      unset($data['panelistId']);
      unset($data['group']);
      unset($data['partner']);

      $response = $this->wsClient->get(
        'KantarConsent/ConsentInvite/' . $partner .
          '/ConsentGroup/' . $consentGroup . '/Panelist/' . $panelistId . '/Status',
        $data,
        ['urlParams' => NULL]
      );
      return $response;
    }
    else {
      return FALSE;
    }
  }
}
