<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Serialization\Json;

/**
 * DeeplinkBuilder.php
 * 
 * Provides methods for detecting mobile devices and creating deeplinks into the app.
 */
class DeeplinkBuilder {
  const APP_SCHEME = 'lifepoints';
  const APP_PACKAGE = 'com.lightspeedgmi.lifepoints';

  const BRANCH_BASE_URL = 'https://api2.branch.io';

  // Defined constants specifying environments.
  const NONE = 0;
  const ANDROID = 1;
  const IOS = 2;
  const ALL = 3;

  /**
   * The request.
   *
   * @var Symfony\Component\HttpFoundation
   */
  protected $request;

  /**
   * If the deeplinks are enabled/disabled on the site currently.
   */
  protected $enabled;

  /**
   * If the deeplinks are enabled/disabled for registration links.
   */
  protected $regEnabled;

  /**
   * The branch.io key, as read in by an environment variable.
   */
  protected $branch_key;

  /**
   * Constructor to initialize the object.
   */
  public function __construct($request) {
    $this->request = $request->getCurrentRequest();

    $config_settings = \Drupal::service('settings')->get('lp');
    try {
      $links_enabled = isset($config_settings['mobile_links']['enabled'])
        ? $config_settings['mobile_links']['enabled'] : self::NONE;

      if (\is_numeric($links_enabled)) {
        $this->enabled = (int) $links_enabled;
      }
      else {
        $this->enabled = ((bool) $links_enabled) ? self::ALL : self::NONE;
      }
    }
    catch (\Throwable $e) {
      $this->enabled = self::NONE;
    }

    try {
      $this->regEnabled = isset($config_settings['mobile_links']['reg_enabled'])
        ? (bool) $config_settings['mobile_links']['reg_enabled'] : FALSE;
    }
    catch (\Throwable $e) {
      $this->regEnabled = FALSE;
    }

    try {
      $this->branch_key = isset($config_settings['mobile_links']['branch_key'])
        ? $config_settings['mobile_links']['branch_key'] : '';
    }
    catch (\Throwable $e) {
      $this->branch_key = '';
    }

  }

  /**
   * create()
   * 
   * Returns a string url for a deeplink to a given action
   * and parameters if applicable in the current environment, or ''
   * (empty string) if not a valid environment.
   * 
   * @param string $action
   *   The app action to be taken.
   * @param array $query
   *   The query parameter array to be processed; for instance,
   *   \Drupal::request()->query->all().
   * @param bool $useBranch
   *   Whether to create a deeplink using branch.io or not.
   * @param string $fallbackUrl
   *   The url to embed in the branch link data in case the app is not installed.
   *
   * @return string
   *   string containing the deep link.
   */
  public function create($action, $query, $useBranch = FALSE, $fallbackUrl = NULL) {
    $url = '';
    // Test for mobile device.
    if ($this->supportsDeeplinks()) {

      if (!empty($query)) {
        $action .= '?' . UrlHelper::buildQuery($query);
      }
      // TM Enable android intent when available.
      // if ($os == 'android') {
      //   $app_link = "intent://$action#Intent;scheme=" . self::APP_SCHEME . ';package=' . self::APP_PACKAGE . ';';
      // }
      // else {
      $url = self::APP_SCHEME . '://'. $action;
      // }

      if ($useBranch && ($branchLink = $this->createBranchDeeplink($url, $fallbackUrl, array_merge( ['action' => $action ], $query)))) {
        // Include the query in the deeplink as well.
        $url = $branchLink . '?' . UrlHelper::buildQuery($query);
      }
    }

    return $url;
  }

  /**
   * library()
   * 
   * Attaches the deeplink script library to the page.
   * 
   *
   * @return void
   */
  public function library() {
    return 'lp_lib/lp_lib_deeplink_scripts';
  }

  /**
   * supportsDeeplinks()
   * 
   * Determines whether or not the current environment supports deeplinks.
   * 
   * @return boolean
   */
  public function supportsDeeplinks() {
    // Check configuration to see if deep links are enabled.
    if (!$this->enabled) {
      return false;
    }

    $os = $this->getEnvironment();
    return (($os == 'ios' && $this->enabled & self::IOS)
      || ($os == 'android' && $this->enabled & self::ANDROID));
  }

  /**
   * supportsRegistration()
   * 
   * Determines whether or not the current environment supports deeplinks for registration as well.
   * 
   * @return boolean
   */
  public function supportsRegistration() {
    // Check configuration to see if deep links are enabled.
    return $this->supportsDeeplinks() && $this->regEnabled;
  }

  /**
   * Determines the current user browser environment.
   */
  public function getEnvironment() {
    //if (/Android|iPhone|iPad/i.test(navigator.userAgent))
    $ua = $this->request->headers->get('user-agent');
    if (empty($ua)) {
      return 'unknown';
    }
    if (preg_match("/Android/i", $ua) === 1) {
      return 'android';
    } else if (preg_match('/iPhone|iPad|iPod/i', $ua) === 1) {
      return 'ios';
    }
    return 'other';
  }

  /**
   * Calls branch to create a deeplink url.
   * 
   * https://help.branch.io/developers-hub/docs/deep-linking-api#section-creating-a-deep-linking-url
   */
  protected function createBranchDeeplink($url, $fallbackUrl, $data) {

    // Confirm we have a branch.io key set up.
    if (empty($this->branch_key)) {
      return '';
    }

    // Create the request object to get the key.
    $client = \Drupal::httpClient();

    $branch_url = self::BRANCH_BASE_URL . '/v1/url';
    $data['$deeplink_path'] = $url;
    if (!empty($fallbackUrl)) {
      $data['$fallback_url'] = $fallbackUrl;
    }
    $branch_data = [
      'branch_key' => $this->branch_key,
      'source' => 'portal',
      'feature' => explode('?', $data['action'])[0],
      'type' => 1,
      'data' => $data,
    ];

    try {
      // Send a Guzzle request using json data format.
      $request = $client->post($branch_url, [
        'json' => $branch_data,
      ]);
      $response = $request->getBody();

      if (!empty($response)) {
        try {
          $redirect_url = Json::decode($response)['url'];
          $this->logData('POST', $branch_url, $branch_data, [], $response);
          return $redirect_url;
        }
        catch (\Throwable $dex) {
          $this->logData('POST', $branch_url, $branch_data, [], $response, $dex->getMessage(), get_class($dex));
          return '';
        }
      }
  
    }
    catch (RequestException $rex) {
      $this->logData('POST', $branch_url, $branch_data, [], $response, $rex->getMessage(), get_class($rex));
      return '';
    }
    
  }

  /**
   * Log request parameter.
   */
  protected function logData($method = NULL, $url = NULL, array $data = [], array $headers = [], $response = NULL, $additionalData = NULL, $type = NULL) {
    $userName = NULL;
    $userSession = \Drupal::service('lp.util.session_user');
    if (isset($data['userName']) && !empty($data['userName'])) {
      $userName = $data['userName'] . ' - ';
    }
    // Extract user email from session.
    elseif ($userSession->isAuthenticated()) {
      $panelistSessionData = $userSession->getPanelistSessionData();
      $userName = $panelistSessionData['emailAddress'] . ' - ';
    }
    $logMsg = '<pre><code>' . $userName . 'Method: ' . $method . ' Request </br>Endpoint: ' . $url . '</br>';

    if (!empty($data)) {
      if (isset($data['password'])) {
        $data['password'] = '*******';
      }
      $logMsg .= 'Data: ' . print_r($data, TRUE);
    }
    if (!empty($headers)) {
      if (isset($headers['x-api-key'])) {
        $headers['x-api-key'] = '*******';
      }
      if (isset($headers['Password'])) {
        $headers['Password'] = '*******';
      }
      $logMsg .= 'Headers: ' . print_r($headers, TRUE);
    }
    if ($response != NULL) {
      $logMsg .= 'Response: ' . print_r($response, TRUE);
    }
    if ($additionalData != NULL) {
      $logMsg .= 'More Info: ' . print_r($additionalData, TRUE);
    }
    $logMsg .= '</code></pre>';
    // Call the POST API method and pass the required parameters.
    if ($type != NULL) {
      $logMsg .= 'Exception Type: ' . $type;
      \Drupal::logger('lp_lib')->error($logMsg);
    }
    else {
      \Drupal::logger('lp_lib')->debug($logMsg);
    }
  }

}