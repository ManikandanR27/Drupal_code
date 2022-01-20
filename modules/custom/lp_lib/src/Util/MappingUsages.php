<?php

namespace Drupal\lp_lib\Util;

use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\ClientException;
use Drupal\smart_ip\SmartIp;
use Drupal\smart_ip_maxmind_geoip2_bin_db\DatabaseFileUtility;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\lp_lib\LifePoints\PanelConfiguration;
use Drupal\Component\Utility\UrlHelper;
use Drupal\lp_lib\LifePoints\DeeplinkBuilder;

/**
 * This class will have all common functions related to mapping.
 */
class MappingUsages {

  const RECRUITMENT_JOIN = 'PortalJoin';

  /**
   *
   */
  public function __construct() {
  }

  /**
   * Function to get language code.
   */
  public function getLanguageCode($languageCharCode) {
    if (empty($languageCharCode)) {
      return FALSE;
    }
    $lanMap = $this->getLanguageMapping();
    if (array_key_exists($languageCharCode, $lanMap)) {
      return $lanMap[$languageCharCode];
    }
    return $languageCharCode;
  }

  /**
   * Function to get country code.
   */
  public function getCountryCode($countryCharCode) {
    if (empty($countryCharCode)) {
      return FALSE;
    }
    $countryMap = $this->getCountryMapping();
    if (array_key_exists($countryCharCode, $countryMap)) {
      return $countryMap[$countryCharCode];
    }
    return FALSE;
  }

  /**
   * Function to get supported language from country code.
   */
  public function getSupportedLanguages($countryCode) {
    switch ($countryCode) {
      case 165:
        return [14, 48];

      break;
      case 119:
        return [14];

      break;
      default:
        return [];
    }
  }

  /**
   * Function to get country name by country code.
   */
  public function getCountryName($countryCode) {
    if (empty($countryCode)) {
      return FALSE;
    }
    $countries = $this->getCountries();
    if (array_key_exists($countryCode, $countries)) {
      return $countries[$countryCode];
    }
    return FALSE;
  }

  /**
   * Function to get Language Name by code.
   */
  public static function getLanguageName($languageCode) {
    if (empty($languageCode)) {
      return FALSE;
    }
    // @todo this looks sloppy/dangerous.
    $languages = $this->getLanguages();
    if (array_key_exists($languageCode, $languages)) {
      return $languages[$languageCode];
    }
    return FALSE;
  }

  /**
   * Get country code,IP, Language if not redirect to logout.
   */
  public static function getCountryByClientIP() {
    $response = MappingUsages::getCountryIPandCode();
    if (!$response) {
      // $logoutUrl = Url::fromRoute('lp_login.Logout');
      $logoutUrl = Url::fromRoute('<front>');
      $redirectResponse = \Drupal::service('redirect_response');
      $redirectResponse->setTargetUrl($logoutUrl->toString() . '?error=ERR_FAILED_COUNTRY_IP_CHECK');
      $redirectResponse->send();
      exit;
    }
    else {
      return $response;
    }
  }

  /**
   * OP-3678.
   * GeoIp2 Library to get country code based on IP
   *
   * @param: [string] Country IP
   *
   * @return [array] List of Country code and IP.
   */
  public static function getCountryByIpGeoLiteBinaryDB($ip) {
    $config = \Drupal::service('settings');
    $moduleHandler = \Drupal::service('module_handler');
    // Check module "Smart IP Bin DB" exist or not.
    if ($moduleHandler->moduleExists('smart_ip_maxmind_geoip2_bin_db')) {

      // Check Geolite lib file exist or not.
      $configSmartIP = \Drupal::config('smart_ip_maxmind_geoip2_bin_db.settings');
      $autoUpdate = $configSmartIP->get('db_auto_update');
      $customPath = $configSmartIP->get('bin_file_custom_path');
      $version = $configSmartIP->get('version');
      $edition = $configSmartIP->get('edition');
      $folder = DatabaseFileUtility::getPath($autoUpdate, $customPath);
      $file = DatabaseFileUtility::getFilename($version, $edition);
      $dbFile = "$folder/$file";

      // Check Geolite lib in desire folder.
      if (!file_exists($dbFile)) {
        \Drupal::logger('LifePoints GeoIP')->error(
          t('Geolite database file %file does not exist or has been moved. Please re-check your Smart IP configuration.', [
            '%file' => $dbFile,
          ])
        );
        return FALSE;
      }

      // Call to smart IP function to get country details.
      $responseData = SmartIp::query($ip);
      if (isset($responseData['countryCode']) && $responseData['countryCode'] != NULL) {

        $returnData = [];
        $returnData['country_code'] = strtoupper($responseData['countryCode']);
        $returnData['country_ip'] = strtoupper($ip);

        $launchedCountriesList = explode(',', $config->get('lp')['launched_countries']['list']);
        if (!in_array($returnData['country_code'], $launchedCountriesList)) {
          // OP-4941 : Don't need this message to be logged.
          // As panelist will see the country not qualified message so we can debug based on it.
          /* \Drupal::logger('LifePoints GeoIP')->error(t('GeoIP! Panel is not available in Launched countries list.') .
          $returnData['country_code'] . ' | ' . $returnData['country_ip'] . ' | ' .
          $config->get('lp')['launched_countries']['list']); */
          \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
          return FALSE;
        }
        return $returnData;
      }
      else {
        \Drupal::logger('LifePoints GeoIP')->error(t('Unable to get Country code from Geolite Country library for IP:') . ' ' . $ip);
        return FALSE;
      }
    }
    else {
      \Drupal::logger('LifePoints GeoIP')->error(t('Sorry! GeoIP is not configured. Please install Smart IP Maxmind Geoip2 Bin DB module.'));
      return FALSE;
    }
  }

  /**
   * MaxMind API call to get Country code from IP based on IP.
   *
   * @param: [string] Country IP
   *
   * @return [array] List of Country code and IP.
   */
  public static function getCountryByIpFromMaxMind($ip) {
    $config = \Drupal::service('settings');
    // Getting setting from Config file.
    $maxmind_geo_ip_config = $config->get('lp')['Maxmind_Geoip_Config'];

    $geoipUrl = "https://geoip.maxmind.com/geoip/v2.1/country/" . $ip . "?pretty";
    $auth = base64_encode($maxmind_geo_ip_config['account_id'] . ":" . $maxmind_geo_ip_config['license_key']);
    $response = \Drupal::httpClient()->get(
      $geoipUrl,
      [
        'headers' => [
          'Authorization' => 'Basic ' . $auth,
        ],
      ]
    );
    $responseData = (string) $response->getBody();
    $responseData = json_decode($responseData, TRUE);

    if (isset($responseData['code'])) {
      \Drupal::messenger()->addError($responseData['error']);
      $urlLogout = Url::fromRoute('lp_login.Logout');
      return new RedirectResponse($urlLogout->toString());
    }
    else {
      if (isset($responseData['country'])) {
        $returnData = [];
        $returnData['country_code'] = strtoupper($responseData['country']['iso_code']);
        $returnData['country_ip'] = strtoupper($responseData['traits']['ip_address']);

        // Changing codes for USA and UK.
        switch ($returnData['country_code']) {
          case 'USA':
            $returnData['country_code'] = 'US';
            break;

          case 'UK':
            $returnData['country_code'] = 'GB';
            break;

          default:
        }
        // Active countries validation and adding country Accept Language.
        $launchedCountriesList = explode(',', $config->get('lp')['launched_countries']['list']);
        if (!in_array($returnData['country_code'], $launchedCountriesList)) {
          // OP-4941 : Don't need this message to be logged.
          // As panelist will see the country not qualified message so we can debug based on it.
          /* \Drupal::logger('LifePoints GeoIP')->error(t('Maxmind! Panel is not available in Launched countries list.') .
          $returnData['country_code'] . ' | ' . $returnData['country_ip'] . ' | ' .
          $config->get('lp')['launched_countries']['list']); */
          \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
          return FALSE;
        }

        return $returnData;
      }
      else {
        \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
        return FALSE;
      }
    }
  }

  /**
   * MaxMind Web Service.
   * Get country code, IP and language from Browser IP.
   *
   * @return [array] List of Country code and IP.
   */
  public static function getCountryIPandCode() {
    $ip = MappingUsages::get_client_ip_address();
    $config = \Drupal::service('settings');
    // Getting setting from Config file.
    $maxmind_geo_ip_config = $config->get('lp')['Maxmind_Geoip_Config'];

    // OP-1698 and possible prep for OP-2046.
    if ($maxmind_geo_ip_config['override_country'] && $maxmind_geo_ip_config['override_ip']) {
      return [
        'country_code' => strtoupper($maxmind_geo_ip_config['override_country']),
        'country_ip' => $maxmind_geo_ip_config['override_ip'],
      ];
    }

    // Execution Priority = 1 | Locale Override.
    // for overriding locale.
    $session_manager = \Drupal::request()->getSession();
    $overrideLocaleData = $session_manager->get('overridelocale');

    if (isset($overrideLocaleData['override']) && $overrideLocaleData['override'] == 'on') {

      return [
        'country_code' => strtoupper($overrideLocaleData['country']),
        'country_ip' => (isset($overrideLocaleData['ip'])) ? $overrideLocaleData['ip'] : $ip,
      ];
    }

    // Execution Priority = 2 | Session Variable.
    // OP-3142.
    // Get country code from user session which set on Login.
    $userSession = \Drupal::service('lp.util.session_user');
    $panelistSessionData = $userSession->getPanelistSessionData();
    // Check for country code found in session then return code & IP.
    if (isset($panelistSessionData['panelistCountryCode']) && $panelistSessionData['panelistCountryCode'] != NULL) {
      return [
        'country_code' => strtoupper($panelistSessionData['panelistCountryCode']),
        'country_ip' => $ip,
      ];
    }

    // Execution Priority = 3 | GeoLite binary DB | Smart IP.
    try {
      // OP-3678
      // Get country based on Geolite Binanny Database | Smart IP.
      $countryData = MappingUsages::getCountryByIpGeoLiteBinaryDB($ip);
      if ($countryData == FALSE) {
        // If GeoIP fail in any case, thron exception call to Maxmind API.
        throw new \Exception();
      }
      else {
        return $countryData;
      }
    }
    catch (\Exception $e) {

      // Execution Priority = 4 | MaxMind API Call.
      // OP-2818 fallback to maxmind geoip.
      try {
        // MaxMind API call to get Country code from IP based on IP.
        return MappingUsages::getCountryByIpFromMaxMind($ip);
      }
      catch (ClientException $e) {
        // print_r($e->getMessage());
        \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
        return FALSE;
      }
    }
  }

  /**
   * Get browser language, given an array of avalaible languages.
   *
   * @param [array] $availableLanguages
   *   Avalaible languages for the site.
   * @param [string] $default
   *   Default language for the site.
   *
   * @return [string] Language code/prefix
   */
  public static function get_browser_language($available = [], $default = 'en') {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      if (empty($available)) {
        return $langs[0];
      }
      foreach ($langs as $lang) {
        $lang = substr($lang, 0, 2);
        if (in_array($lang, $available)) {
          return $lang;
        }
      }
    }
    return $default;
  }

  /**
   * Function to get panel id from settings.
   */
  public static function getPanelId() {
    $config = \Drupal::service('settings');
    // Will return the panel id from setting file.
    return $config->get('lp')['panelId'];
  }

  /**
   * Function to get the client IP address.
   */
  public static function get_client_ip_address() {

    $config = \Drupal::service('settings');
    // Getting setting from Config file.
    $maxmind_geo_ip_config = $config->get('lp')['Maxmind_Geoip_Config'];

    if (isset($maxmind_geo_ip_config['override_country']) &&
      $maxmind_geo_ip_config['override_country'] != '' &&
      isset($maxmind_geo_ip_config['override_ip']) &&
      $maxmind_geo_ip_config['override_country'] != ''
    ) {
      return $maxmind_geo_ip_config['override_ip'];
    }

    // Execution Priority = 1 | Locale Override.
    // For overriding locale.
    $session_manager = \Drupal::request()->getSession();
    $overrideLocaleData = $session_manager->get('overridelocale');
    if (
      isset($overrideLocaleData['override']) &&
      $overrideLocaleData['override'] == 'on' &&
      isset($overrideLocaleData['ip'])
    ) {
      return $overrideLocaleData['ip'];
    }

    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $ip = $request->getClientIp();
    return $ip;
  }

  /**
   * Function to get translation language from panel config.
   */
  public static function get_translation_language($panel_code) {
    if (!empty($panel_code)) {
      $properties['name'] = strtoupper($panel_code);
      $properties['vid'] = 'lp_panels';
      $panel_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $panel_data = reset($panel_data);
      if (!empty($panel_data)) {
        return $panel_data->field_language_for_translation->getValue()[0]['target_id'];
      }
    }
    return \Drupal::languageManager()->getDefaultLanguage()->getId();
  }

  /**
   * Check browser language support or not.
   * If browser language dosen't support then set the default language for panel from settings.
   * Set the language for translations.
   */
  public static function checkPanelAvailability($panel_code) {
    // Check the panel code supoorted with browser lang or not.
    $properties['name'] = strtoupper($panel_code);
    $properties['vid'] = 'lp_panels';
    $panel_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $panelExist = reset($panel_data);
    if (!empty($panelExist)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Function to get panel code by ACCEPT LANGUAGE.
   */
  public static function get_panel_code() {
    // We don't need to check panel for unsubscribe by email.
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route == 'lp_unsubscribe.unsubscribeByEmail') {
      return NULL;
    }

    // OP-3129
    // Change Panel w.r.t to new Alternative Panel code.
    $userSession = \Drupal::service('lp.util.session_user');
    $panelistSessionData = $userSession->getPanelistSessionData();

    // Check for country code found in session then return code & IP.
    if (isset($panelistSessionData['alternativePanelistPanelCode']) && $panelistSessionData['alternativePanelistPanelCode'] != NULL) {
      // Return Panel code.
      return $panelistSessionData['alternativePanelistPanelCode'];
    }
    // OP-7028.
    // Fix for the case when browser language not found.
    // $_SERVER['HTTP_ACCEPT_LANGUAGE']  found null.
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $config = \Drupal::service('settings');
      $country_Data = MappingUsages::getCountryIPandCode();
      $default_lang = isset($config->get('lp')['default_language'][$country_Data['country_code']]) ? $config->get('lp')['default_language'][$country_Data['country_code']] : '';
      $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $default_lang;
      \Drupal::logger('defaultLanguage')->notice($default_lang);
    }

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
      $accept_language_code = substr($http_accept_language, 0, 2);
      $country_Data = MappingUsages::getCountryIPandCode();

      // For overriding locale.
      $session_manager = \Drupal::request()->getSession();
      $overrideLocaleData = $session_manager->get('overridelocale');

      // OP-6474 - Set the panel from JoinAPI url through override on if browser active panel not match.
      // OP-6571 - Multilang country DOI support for organic and portal join as well.
      // Set panel from URL if browser lang doesn't match for DOI in all three types of reg.
      $urlParams = \Drupal::request()->query->all();
      if (isset($urlParams['locale'])) {
        $urlParams['acceptLang'] = $urlParams['locale'];
      }

      $urlAcceptLangParam = isset($urlParams['acceptLang']) ? explode("-", $urlParams['acceptLang']) : [];

      // Validate the jooinapi preregistration call.
      // Panelist should connect to the same country.
      // But the url locale is diffrent with connected locale.
      // If all condition match then set the url accept langauge panel to complete the reg.
      if (
        isset($urlParams['token']) && !empty($urlParams['token']) &&
        isset($urlParams['domain']) && !empty($urlParams['domain']) &&
        isset($urlParams['acceptLang']) && !empty($urlParams['acceptLang']) &&
        (
        stristr(Url::fromRoute('lp_registration.registrationForm')->toString(), \Drupal::request()->getPathInfo()) ||
        stristr(Url::fromRoute('lp_registration.doiemailaccount')->toString(), \Drupal::request()->getPathInfo()) ||
        stristr(Url::fromRoute('lp_registration.doiemailrecruitment')->toString(), \Drupal::request()->getPathInfo())
        ) &&
        !$userSession->isAuthenticated() &&
        (strtolower($country_Data['country_code']) == strtolower($urlAcceptLangParam[1]))
      ) {
        // Set the override on to activate the panel from url locale for joinAPI.
        $overrideLocaleData['override'] = 'on';
        $overrideLocaleData['language'] = strtoupper($urlAcceptLangParam[0]);
        $overrideLocaleData['country'] = strtoupper($urlAcceptLangParam[1]);
        $session_manager->set('overridelocale', $overrideLocaleData);
      }

      // If override locale is on or set on by above join api case.
      if (isset($overrideLocaleData['override']) && $overrideLocaleData['override'] == 'on') {
        $country_Data['country_code'] = $overrideLocaleData['country'];
        $accept_language_code = $overrideLocaleData['language'];
      }
      $countryCode = isset($country_Data['country_code']) ? $country_Data['country_code'] : ' ';
      $panel_code = $countryCode . '_' . $accept_language_code;

      if (MappingUsages::checkPanelAvailability($panel_code)) {
        return strtoupper($panel_code);
      }
      else {
        // Check the panel code with default lang from config.
        $config = \Drupal::service('settings');
        // Getting the default lang for particular country.
        if (isset($country_Data['country_code'])) {
          $default_lang = isset($config->get('lp')['default_language'][$country_Data['country_code']]) ? $config->get('lp')['default_language'][$country_Data['country_code']] : '';
          $panel_code = $country_Data['country_code'] . '_' . $default_lang;
          if (MappingUsages::checkPanelAvailability($panel_code)) {
            return strtoupper($panel_code);
          }
          else {
            return NULL;
          }
        }
        else {
          return NULL;
        }
      }
    }
    else {
      return NULL;
    }
  }

  /**
   * Function to Get state name from Taxonomy ID.
   */
  public static function getStateLocalCodeByStateTid($tid) {
    if ($tid && is_numeric($tid)) {
      // Get Drupal Language set.
      $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      // Load Taxonomy Term based on Term ID.
      $term = Term::load($tid);
      // It will be in english.
      $state_name = $term->getName();
      // State native will be in english if language EN.
      $state_native = $state_name;
      // Check translation if exist then modify the state native.
      if ($term->hasTranslation($drupal_language)) {
        // Get the translated term.
        $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $drupal_language);
        $state_native = $translated_term->getName();
      }

      // Get state local code wrt to State TID.
      $state_local_code_tid = $term->field_lp_state_local_code->getValue()[0]['target_id'];
      $state_local_code = Term::load($state_local_code_tid)->get('name')->value;

      $state['state_name'] = $state_name;
      $state['state_native'] = $state_native;
      $state['state_local_code'] = $state_local_code;
      return $state;
    }
  }

  /**
   * Function to Get City name from Taxonomy ID.
   */
  public static function getCityLocalCodeByCityTid($tid) {
    if ($tid && is_numeric($tid)) {
      // Get Drupal Language set.
      $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      // Load Taxonomy Term based on Term ID.
      $term = Term::load($tid);
      $city_name = $term->getName();
      // City native will be in english if language EN.
      $city_native = $city_name;
      // Check translation if exist then modify the city native.
      if ($term->hasTranslation($drupal_language)) {
        // Get the translated term.
        $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $drupal_language);
        $city_native = $translated_term->getName();
      }

      // Get state local code wrt to State TID.
      $city_local_code_tid = $term->field_lp_city_local_code->getValue()[0]['target_id'];
      $city_local_code = Term::load($city_local_code_tid)->get('name')->value;

      $city['city_name'] = $city_name;
      $city['city_native'] = $city_native;
      $city['city_local_code'] = $city_local_code;
      return $city;
    }
  }

  /**
   * Function to Get City name from Taxonomy ID.
   */
  public static function getHumanReadableMessage($msg) {
    if ($msg != NULL || $msg != '') {
      $msg = str_replace('_', ' ', $msg);
      $msg = rtrim($msg, '.') . '.';
      $new_msg = ucfirst($msg);
      return $new_msg;
    }
    else {
      return $msg;
    }
  }

  /**
   * Utility: find term by name and vid.
   *
   * @param array $fields
   *   Term name.
   * @param null $vid
   *   Term vid.
   *
   * @return int
   *   Term id or 0 if none.
   */
  public static function getTermIdByFieldNameAndValue(array $fields = [], $vid = NULL, $get_full_detail = FALSE) {
    $properties = [];
    if (!empty($fields)) {
      foreach ($fields as $fieldName => $fieldValue) {
        $properties[$fieldName] = $fieldValue;
      }
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);

    $term = reset($terms);

    if ($get_full_detail) {
      return $term;
    }
    else {
      return !empty($term) ? $term->id() : 0;
    }
  }

  /**
   * Utility: get node details by path.
   *
   * @param $node_path
   *   Term name
   * @param array $request_fields
   *   request fields.
   */
  public static function getNodeDetailsByPath($node_path) {
    $path = \Drupal::service('path_alias.manager')->getPathByAlias($node_path);
    $node = '';
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = Node::load($matches[1]);
    }
    return $node;
  }

  /**
   * Function to return array of recruitment params.
   */
  public static function getRecruitmentFieldsList() {
    // URLParam => endpointparam.
    $recruitmentField = [
      'advertiser_id' => 'advertiserId',
      'advertiser_ref' => 'advertiserRef',
      'aff_click_id' => 'affClickId',
      'aff_id' => 'affiliateId',
      'aff_sub' => 'publisherSubId',
      'aff_sub2' => 'publisherSubId2',
      'aff_sub3' => 'publisherSubId3',
      'aff_sub4' => 'publisherSubId4',
      'aff_sub5' => 'publisherSubId5',
      'aff_unique1' => 'publisherUnique1',
      'aff_unique2' => 'publisherUnique2',
      'aff_unique3' => 'publisherUnique3',
      'aff_unique4' => 'publisherUnique4',
      'aff_unique5' => 'publisherUnique5',
      'affiliate_id' => 'affiliateId',
      'affiliate_name' => 'affiliateName',
      'affiliate_ref' => 'affiliateReferenceId',
      'country_code' => 'countryCode',
      'currency' => 'currency',
      'date' => 'date',
      'datetime' => 'dateTime',
      'file_id' => 'fileId',
      'file_name' => 'fileName',
      'ip' => 'iPAddress',
      'mobile_carrier' => 'mobileCarrier',
      'offer_file_id' => 'offerFileId',
      'offer_id' => 'offerID',
      'offer_name' => 'offerName',
      'offer_ref' => 'offerReferenceId',
      'offer_url_id' => 'offerURLId',
      'payout' => 'offerPayout',
      'ran' => 'ran',
      'referer' => 'referer',
      'region_code' => 'regionCode',
      'revenue' => 'revenue',
      'source' => 'source',
      'time' => 'time',
      'transaction_id' => 'transactionId',
      'user_agent' => 'userAgent',
      'XP_utm_source' => 'sourceTrackParam',
      'XP_utm_medium' => 'mediumTrackParam',
      'XP_utm_campaign' => 'campaignTrackParam',
      'XP_utm_term' => 'termTrackParam',
      'lang' => 'languageCode',
      'country' => 'country',
      'contactEmail' => 'contactEmail',
      'contact_email' => 'contact_email',
      'entryurltype' => 'entryurltype',
      'entryurl' => 'entryurl',
    ];
    return $recruitmentField;
  }

  /**
   * Function to return array of join api param.
   */
  public static function getJoinAPIFieldsList($joinType = NULL) {
    // Urlparam => endpointParam.
    $preRegistrationFields = [
      'contactEmail' => 'userName',
      'title' => 'title',
      'firstName' => 'firstName',
      'lastName' => 'lastName',
      'streetAddress' => 'streetAddress1',
      'streetAddress2' => 'streetAddress2',
      'country' => 'country',
      'lang' => 'lang',
      'phoneNumber' => 'phoneNumber',
      'postalCode' => 'postalCode',
      'contactCity' => 'city',
      'state' => 'state',
      'source' => 'recruitmentSource',
      'ip' => 'ip',
      'joinType' => 'joinType',
      'gender' => 'gender',
      'dateOfBirth' => 'dateOfBirth',
      'userName' => 'userName',
      'address1' => 'streetAddress1',
      'address2' => 'streetAddress2',
      'termsVersion' => 'termsVersion',
    ];
    // OP-7123: Portal: Send City and State on the Post PreRegistration API
    if ($joinType == self::RECRUITMENT_JOIN) {
      $cityStateFields = [
        'city' => 'city',
        'cityEnglish' => 'cityEnglish',
        'cityNative' => 'cityNative',
        'cityCode' => 'cityCode',
        'stateEnglish' => 'stateEnglish',
        'stateNative' => 'stateNative',
        'stateCode' => 'stateCode',
      ];
      $preRegistrationFields = array_merge($preRegistrationFields, $cityStateFields);
    }
    return $preRegistrationFields;
  }

  /**
   * Function to return fields array for Token Login.
   */
  public static function getTokenLoginFieldsList() {
    // Formparam => endpointParam.
    $tokenLoginFields = [
      'token' => 'token',
      'ip' => 'ip',
      'isFullIpCheckEnabled' => 'isFullIpCheckEnabled',
    ];
    return $tokenLoginFields;
  }

  /**
   * My Activity change Date Format (required MM-DD-YYYY HH:MM AM/PM)
   */
  public static function myActivityConvertDataTimeFormat($date) {
    if (isset($date) && $date != NULL) {
      $date = explode('.', $date);
      // Removing T from date.
      $date = $date[0];
      $date2 = str_replace('T', ' ', $date);

      // Convert date and time to seconds.
      $timeInSec = strtotime($date2);
      // Convert seconds into a specific format "12/30/2018 05:37 PM".
      $newDate = date("m/d/Y h:i A", $timeInSec);
      return $newDate;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Repository for Cookie Description and expiry date w.r.t cookie ident.
   *
   * @param [string] $cookieIdent
   *   Field Type name.
   *
   * @return [string] array with description and expiry date
   */
  public static function getDecriptionAndExpirydate($cookieIdent) {
    $cookie_details = [];
    switch ($cookieIdent) {
      case '27265c42-7182-4d08-83ff-8a865fec135f':
        $cookie_details['desc'] = 'Testing description for 27265c42-7182-4d08-83ff-8a865fec135f';
        $cookie_details['expirydate'] = '365days';
        return $cookie_details;

      case 'a2e676a4-8eb6-4d5c-8055-437b99ded87a':
        $cookie_details['desc'] = 'Testing description for a2e676a4-8eb6-4d5c-8055-437b99ded87a';
        $cookie_details['expirydate'] = '365days';
        return $cookie_details;

      case '3':
        $cookie_details['desc'] = 'Testing description for 3';
        $cookie_details['expirydate'] = '768days';
        return $cookie_details;

      default:
        return TRUE;
    }
  }

  /**
   * Generates the unique string i.e. uuid for curiosity popup.
   *
   * @return [string] alpha numeric 32 bit long uuid
   */
  public static function generate_curiosity_interview_uuid() {
    return sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  /**
   * Get available Active Panels based on country selection.
   *
   * @param string
   *   Country code
   *
   * @return array
   *   List of a country's panels indexed by name, including accept-lang and active state.
   *   If the country is not found, an empty array will be returned.
   */
  public static function getPanelListByCountryCode(string $countryCode) {
    // Get Country code Taxonomy ID by country code.
    $country_abbrev_tid = MappingUsages::getTermIdByFieldNameAndValue(
      [
        'name' => $countryCode,
      ],
      'lp_country_abbrev'
    );

    $panels = [];

    $properties['field_lp_country_abbrev'] = $country_abbrev_tid;
    $properties['vid'] = 'lp_panels';

    // Get Country code Taxonomy ID by country code.
    $lp_panels_tids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);

    // Determine the default language for the given county code.
    $config = \Drupal::service('settings');
    $default_lang = isset($config->get('lp')['default_language'][$countryCode]) ?
      $config->get('lp')['default_language'][$countryCode] : NULL;

    // Get list of Panels.
    foreach ($lp_panels_tids as $term) {
      if (!empty($term)) {
        // Get Panel Name and some other identifying properties.
        $panels[$term->getName()] = [
          'name' => $term->getName(),
          'tid' => $term->tid->getValue()[0]['value'],
          'uuid' => $term->uuid->getValue()[0]['value'],
          'field_lp_accept_language' => $term->field_lp_accept_language->getValue()[0]['value'],
          'field_lp_country_abbrev' => $country_abbrev_tid,
          'field_lp_panel_available' => (boolean) $term->field_lp_panel_available->getValue()[0]['value'],
          'is_default' => (substr($term->field_lp_accept_language->getValue()[0]['value'], 0, 2) == $default_lang),
        ];
      }
    }

    return $panels;

  }

  /**
   * Get available Active Panels based on country selection.
   *
   * @param: [string] Country code
   *
   * @return [array] List of active panel's accept language.
   */
  public static function getPanelAcceptLangListByCountryCode($countryCode, $panelAcceptLang = NULL) {

    $altAcceptLang = [];
    $altPanels = [];

    // Get Country code Taxonomy ID by country code.
    $lp_panels_tids = MappingUsages::getPanelListByCountryCode($countryCode);

    // Get list of active Panel Accept language.
    foreach ($lp_panels_tids as $name => $term) {
      if (!empty($term)) {
        // Only for active Panel in system.
        if (isset($term['field_lp_panel_available']) && $term['field_lp_panel_available'] == TRUE) {

          if (isset($term['field_lp_accept_language'])) {
            if (empty($panelAcceptLang) || $panelAcceptLang !== $term['field_lp_accept_language']) {
              // Get Accept Language.
              $altAcceptLang[] = $term['field_lp_accept_language'];
              // Get Panel Name.
              $altPanels[] = $name;
            }
          }
        }
      }
    }
    if (!empty($altAcceptLang)) {
      return $altAcceptLang;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get validation checks from settings or override tool.
   * Pass checkConfig as false where need to perform validation checks only from override locale tool.
   */
  public static function getQualityValidationChecks($checkConfig = TRUE) {
    // Validation checks form settings.
    // Defaut value of checkConfig is true.
    // Pass checkConfig as false where need to perform validation checks only from override locale tool.
    $configValidationChecks = [];
    if ($checkConfig == TRUE) {
      $config = \Drupal::service('settings');
      $configValidationChecks = $config->get('lp')['validation_checks'];
    }

    // For overriding locale.
    $session_manager = \Drupal::request()->getSession();
    $overrideLocaleData = $session_manager->get('overridelocale');
    if (isset($overrideLocaleData['override']) && $overrideLocaleData['override'] == 'on') {
      if (isset($overrideLocaleData['qualityValidationChecks']['emailValidation']) && $overrideLocaleData['qualityValidationChecks']['emailValidation'] != '') {
        $configValidationChecks['emailValidation'] = $overrideLocaleData['qualityValidationChecks']['emailValidation'];
      }
      if (isset($overrideLocaleData['qualityValidationChecks']['ipCheck']) && $overrideLocaleData['qualityValidationChecks']['ipCheck'] != '') {
        $configValidationChecks['ipCheck'] = $overrideLocaleData['qualityValidationChecks']['ipCheck'];
      }
      if (isset($overrideLocaleData['qualityValidationChecks']['addressCheck']) && $overrideLocaleData['qualityValidationChecks']['addressCheck'] != '') {
        $configValidationChecks['addressCheck'] = $overrideLocaleData['qualityValidationChecks']['addressCheck'];
      }
      if (isset($overrideLocaleData['qualityValidationChecks']['isFullIpCheckEnabled']) && $overrideLocaleData['qualityValidationChecks']['isFullIpCheckEnabled'] != '') {
        $configValidationChecks['isFullIpCheckEnabled'] = $overrideLocaleData['qualityValidationChecks']['isFullIpCheckEnabled'];
      }
      if (isset($overrideLocaleData['qualityValidationChecks']['maxIpCapCheck']) && $overrideLocaleData['qualityValidationChecks']['maxIpCapCheck'] != '') {
        $configValidationChecks['maxIpCapCheck'] = $overrideLocaleData['qualityValidationChecks']['maxIpCapCheck'];
      }
      if (isset($overrideLocaleData['qualityValidationChecks']['smartScreenChecks']) && $overrideLocaleData['qualityValidationChecks']['smartScreenChecks'] != '') {
        $configValidationChecks['smartScreenChecks'] = $overrideLocaleData['qualityValidationChecks']['smartScreenChecks'];
      }
      // Config set for NuDetect.
      if (isset($overrideLocaleData['qualityValidationChecks']['nuDetect']) && $overrideLocaleData['qualityValidationChecks']['nuDetect'] != '') {
        $configValidationChecks['nuDetect'] = $overrideLocaleData['qualityValidationChecks']['nuDetect'];
      }
    }
    return $configValidationChecks;
  }

  /**
   * Get all acceptable PS reason list.
   *
   * @return string: Comma seperate PS reason code
   */
  public static function acceptablePsReasonsList() {
    $properties['field_lp_portal_behavior'] = 'can_log_in';
    $properties['vid'] = 'lp_portal_behavior';
    $psReasons = '';
    $lpPsReasonsTids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    foreach ($lpPsReasonsTids as $term) {
      if (!empty($term)) {
        $psReasons = $term->getName() . ',' . $psReasons;
      }
    }
    if (!empty($psReasons)) {
      return rtrim($psReasons, ",");
    }
    else {
      return $psReasons;
    }
  }

  /**
   * Returns tids of other options of city dropdown
   * OP-3992: LP Registration CN and BR - add open end box for city=other.
   */
  public static function getOtherOptionsTid() {
    // Get the current panel from which panelist is connected.
    $panelCode = MappingUsages::get_panel_code();
    $panelCode = explode("_", $panelCode);
    $properties = $states = $cities = [];
    // Get Drupal Language set.
    $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get tid of country abbrev taxonomy terms.
    $properties['name'] = $panelCode[0];
    $properties['vid'] = 'lp_country_abbrev';
    $country_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $country_data = reset($country_data);
    if (isset($country_data) && !empty($country_data)) {
      $country_abbrev_tid = $country_data->toArray()['tid'][0]['value'];
    }
    // Setting up the properties.
    $state_properties['field_lp_country_abbrev'] = $country_abbrev_tid;
    $state_properties['vid'] = 'lp_state';
    // Load the state taxonomy based on country.
    $state_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($state_properties);
    foreach ($state_data as $data) {
      $term_name = $data->getName();
      $state_local_code = $data->field_lp_state_local_code->getValue()[0]['target_id'];
      $states[$state_local_code] = $term_name;
    }
    foreach ($states as $key => $value) {
      $city_properties['field_lp_state_local_code'] = $key;
      $city_properties['vid'] = 'lp_city';
      $city_properties['name'] = 'Other';
      // Load the city data based on properties.
      $city_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($city_properties);
      foreach ($city_data as $tid => $city) {
        if ($city->hasTranslation($drupal_language)) {
          $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($city, $drupal_language);
          $term_name = $translated_term->getName();
        }
        else {
          $term_name = $city->getName();
        }
        // $cities[$tid] = $term_name;
        $cities[] = $tid;
      }
    }
    return $cities;
  }

  /**
   * Add Drupal logger when execption occurred during any process.
   *
   * @param [array] $exceptionInfo
   *   Exception/error information.
   * @param [array] $data
   *   required information while submit form/action.
   */
  public static function exceptionLogInfo($exceptionInfo, $data = NULL) {
    $attributes = \Drupal::service('request_stack')->getCurrentRequest()->attributes->all();
    $userSession = \Drupal::service('lp.util.session_user');
    $logMsg = '<pre>';
    // Adding submittied Form data in Log.
    if (isset($data['userName']) && !empty($data['userName'])) {
      $userName = $data['userName'] . ' - ';
      $logMsg .= "<p>" . $userName . " Error details - panelist more details:";
    }

    if (!empty($data)) {
      if (isset($data['password'])) {
        $data['password'] = '*******';
      }
      $logMsg .= "<p>Data: " . print_r($data, TRUE) . "</p>";
    }

    // Panelist session data.
    if ($userSession->isAuthenticated()) {
      $logSessionData = [];
      $panelistSessionData = $userSession->getPanelistSessionData();
      $logSessionData['panelistId'] = $panelistSessionData['panelistId'];
      $logSessionData['emailAddress'] = $panelistSessionData['emailAddress'];
      $logSessionData['firstName'] = $panelistSessionData['firstName'];
      $logSessionData['lastName'] = $panelistSessionData['lastName'];
      $logSessionData['panelistActivePanel'] = $panelistSessionData['panelistActivePanel'];
      $logSessionData['panelistCountryCode'] = $panelistSessionData['panelistCountryCode'];
      $logSessionData['panelistIp'] = $panelistSessionData['panelistIp'];
      // To do: add require info.
      $logMsg .= "Panelist Session Data: " . print_r($logSessionData, TRUE);
    }

    $logMsg .= "<p>Error message: " . $exceptionInfo->getMessage() . "</p>";
    $logMsg .= "<p>Controller: " . $attributes['_controller'] . "</p>";
    $logMsg .= "<p>Route: " . $attributes['_route'] . "</p>";
    $logMsg .= "<p>Current Path: " . \Drupal::service('path.current')->getPath() . "</p>";
    $logMsg .= "<p>Referer Path: " . \Drupal::request()->server->get('HTTP_REFERER') . "</p>";
    $logMsg .= "<p>File: " . $exceptionInfo->getFile() . "</p>";
    $logMsg .= "<p>Line: " . $exceptionInfo->getLine() . "</p>";
    $logMsg .= "<p>Error code: " . $exceptionInfo->getCode() . "</p>";
    $traceArray = explode("\n", $exceptionInfo->getTraceAsString());
    if (isset($traceArray[0])) {
      $logMsg .= "<p>Trace Error line: " . $traceArray[0] . "</p>";
    }
    if (isset($traceArray[1])) {
      $logMsg .= "<p>Trace Error line: " . $traceArray[1] . "</p>";
    }
    $logMsg .= '</pre>';
    \Drupal::logger('lp_exception ')->error($logMsg);
  }

  /**
   * Get recruitment monus points configuration .*/
  public static function getRecruitmentBonusPilotConfig($affiliateId = NULL) {

    // Get panelist active panel.
    $panelCode = '';
    $panelCode = $panelCode = MappingUsages::get_panel_code();

    // Get the client browser time from js.
    $currentTime = \Drupal::time()->getCurrentTime();
    // Get the config values from camapaign configuration form.
    $config = \Drupal::config('lp_campaign.recruitmenbonuspilotsettings');
    $panel = $config->get('recruitment_panel_check_required');
    $bonusCheckRequired = $config->get('recruitment_bonus_check_required');
    $nonOrgLeftBlock = $config->get('recruitment_non_org_left_block');
    $regPendingPointsWaitingText = $config->get('recruitment_reg_pending_points_waiting_text');
    $regConfirmPointsEarningText = $config->get('recruitment_reg_confirm_points_earning_text');
    $regAffiliateid = $config->get('recruitment_offer_id');
    $regAffiliateIds = explode(',', $regAffiliateid);
    $start_date = $config->get('recruitment_start_date');
    $end_date = $config->get('recruitment_end_date');
    // OP-5919 - As affliated session but value of recruitment bonus is not displayed.
    $session_manager = \Drupal::request()->getSession();
    $joinApiParams = $session_manager->get("joinApiParams");
    if (isset($joinApiParams['affiliate_id']) &&  $joinApiParams['affiliate_id'] == $regAffiliateid) {
      $affiliateIdSession = TRUE;
    }
    $result = [];
    if (($start_date <= $currentTime && $end_date >= $currentTime)
        && (in_array($panelCode, $panel))
        && (!empty($bonusCheckRequired) && ($bonusCheckRequired['recruitmentbonuspilot'] != "0"))
        && ((in_array($affiliateId, $regAffiliateIds)) || ($affiliateIdSession))
      ) {
      $result = [
        'nonOrgLeftBlock' => $nonOrgLeftBlock,
        'regPendingPointsWaitingText' => $regPendingPointsWaitingText,
        'regConfirmPointsEarningText' => $regConfirmPointsEarningText,
      ];
    }

    return $result;
  }

  /**
   * Recreate URL with Base languge (Accept Language) for default locale "EN" comes on URL.
   * Else get locale from Routing.
   *
   * Param: RouteName URL (String)
   *
   * @return: New Recreated URL Routing.
   */
  public static function recreateUrlWithLocaleForDefaultPanel($routeName, $routeParams = []) {

    // Get Route name from Menu Routing with query Paramters.
    $redirectUrl = Url::fromRoute($routeName, isset($routeParams) ? $routeParams : [])->toString();

    // Get list of active URL prefis from Language detedction.
    $urlPrefixes = \Drupal::config('language.negotiation')->get('url.prefixes');

    // Get languageCode from URL.
    $languagecode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get current default langauge.
    $default_languagecode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $defaultPrefix = $urlPrefixes[$default_languagecode];

    // Get panel from Maxmind Panel code calucation ie. Base Accept Lang.
    $panelConfiguration = new PanelConfiguration();
    $panelAcceptLang = $panelConfiguration->getAcceptLanguage();

    // Get URL prefex for current panel by validating through Lang Detection list.
    // Note that Accept-lang is a case-insensitive http parameter, but array keys are case-sensitive,
    // so we must enumerate through them.
    $desiredPrefix = FALSE;
    foreach ($urlPrefixes as $lang => $prefix) {
      if (strtolower($lang) == strtolower($panelAcceptLang)) {
        $desiredPrefix = $prefix;
        break;
      }
    }

    // Modify URL if only lang code detected. Add Current locale.
    if (strcasecmp($languagecode, $default_languagecode) == 0) {
      // Search Accept lang in Language detection list of Panels.
      if ($desiredPrefix != FALSE) {
        // Replace on the first occurence of default lang code if exits.
        return self::str_replace_first('/' . $defaultPrefix, '/' . $desiredPrefix, $redirectUrl);
      }
    }

    // Ignore case for perfect locale i.e. <langCode>-<CountryCode> like en-us, ca-fr.
    elseif (preg_match('/^\/[a-z]{2}\-[a-z]{2}(\/.*)?/', $redirectUrl)) {
      // Nothing to do. Same original URL.
    }

    // When no locale in URL but sustem detect default EN as a panel code.
    elseif (preg_match('/^(\/' . $defaultPrefix . ')/', $redirectUrl)) {
      if ($desiredPrefix != FALSE) {
        // Replace on the first occurence of default lang code if exits.
        return self::str_replace_first('/' . $defaultPrefix, '/' . $desiredPrefix, $redirectUrl);
      }
    }
    // Return same Route URL when nothing matched.
    return $redirectUrl;
  }

  /**
   * String Replace with first occurence.
   *
   * @params:
   * $search: The value being searched for.
   * replace : The replacement value that replaces found search values.
   * subject: The string or array being searched and replaced on.
   */
  public static function str_replace_first($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== FALSE) {
      return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
  }

  /**
   * Get Language Code from Current path URL or
   * Accept Language if Locale in URL or missing Locale(en-us, en-gb) in URL.
   *
   * @return: Language Code.
   */
  public static function getLocaleForDefaultPanel() {

    // Get Current Path.
    $currentPathUrl = \Drupal::requestStack()->getCurrentRequest()->getPathInfo();

    // Get list of active URL prefis from Language detedction.
    $urlPrefixes = \Drupal::config('language.negotiation')->get('url.prefixes');

    // Get languageCode from URL.
    $languagecode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get current default langauge.
    $default_languagecode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $defaultPrefix = $urlPrefixes[$default_languagecode];

    // Get panel from Maxmind Panel code calucation ie. Base Accept Lang.
    $panelConfiguration = new PanelConfiguration();
    $panelAcceptLang = $panelConfiguration->getAcceptLanguage();

    // Get URL prefex for current panel by validating through Lang Detection list.
    // Note that Accept-lang is a case-insensitive http parameter, but array keys are case-sensitive,
    // so we must enumerate through them.
    $desiredPrefix = FALSE;
    foreach ($urlPrefixes as $lang => $prefix) {
      if (strtolower($lang) == strtolower($panelAcceptLang)) {
        $desiredPrefix = $prefix;
        break;
      }
    }

    // Check in URL if only lang code detected. Get Current locale.
    if (strcasecmp($languagecode, $default_languagecode) == 0) {
      // Search Accept lang in Language detection list of Panels.
      if ($desiredPrefix != FALSE) {
        return $desiredPrefix;
      }
    }

    // Ignore case for perfect locale i.e. <langCode>-<CountryCode> like en-us, ca-fr.
    elseif (preg_match('/^\/[a-z]{2}\-[a-z]{2}(\/.*)?/', $currentPathUrl)) {
      return $languagecode;
    }

    // When no locale in URL but system detect default EN as a panel code.
    elseif (preg_match('/^(\/' . $defaultPrefix . '\/)/', $currentPathUrl)) {
      if ($desiredPrefix != FALSE) {
        return $defaultPrefix;
      }
    }
    else {
      return $languagecode;
    }
  }

  /**
   * Remove Language Code from Current path URL or
   * Accept Language if Locale in URL. Helpfull for Passbacl URL.
   *
   * @return: Language Code.
   */
  public static function removeLangLocaleFromURL($redirectUrl, $routeParams = [], $urlIsRouteName = TRUE) {

    // Get Route name from Menu Routing with query Paramters.
    if ($urlIsRouteName) {
      $redirectUrl = Url::fromRoute($redirectUrl, isset($routeParams) ? $routeParams : [])->toString();
      // Do nothing.
    }

    // Get list of active URL prefis from Language detedction.
    $urlPrefixes = \Drupal::config('language.negotiation')->get('url.prefixes');

    // Get languageCode from URL.
    $languagecode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get current default langauge.
    $default_languagecode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $defaultPrefix = $urlPrefixes[$default_languagecode];

    // Get panel from Maxmind Panel code calucation ie. Base Accept Lang.
    $panelConfiguration = new PanelConfiguration();
    $panelAcceptLang = $panelConfiguration->getAcceptLanguage();

    // Get URL prefex for current panel by validating through Lang Detection list.
    // Note that Accept-lang is a case-insensitive http parameter, but array keys are case-sensitive,
    // so we must enumerate through them.
    $desiredPrefix = FALSE;
    foreach ($urlPrefixes as $lang => $prefix) {
      if (strtolower($lang) == strtolower($panelAcceptLang)) {
        $desiredPrefix = $prefix;
        break;
      }
    }

    // Modify URL if only lang code detected. Add Current locale.
    if (strcasecmp($languagecode, $default_languagecode) == 0) {
      // Search Accept lang in Language detection list of Panels.
      if ($desiredPrefix != FALSE) {
        // Replace on the first occurence of default lang code if exits.
        return self::str_replace_first('/' . $defaultPrefix . '/', '/', $redirectUrl);
      }
    }

    // Ignore case for perfect locale i.e. <langCode>-<CountryCode> like en-us, ca-fr.
    elseif (preg_match('/^\/[a-z]{2}\-[a-z]{2}(\/.*)?/', $redirectUrl)) {
      // Lang from Negotian class with accept lang from Lang detection.
      $langFromURL = $urlPrefixes[$languagecode];
      if (strcasecmp($languagecode, $langFromURL) == 0) {
        return self::str_replace_first('/' . $langFromURL . '/', '/', $redirectUrl);
      }
      else {
        return self::str_replace_first('/' . strtolower($languagecode) . '/', '/', $redirectUrl);
      }
    }

    // When no locale in URL but sustem detect default EN as a panel code.
    elseif (preg_match('/^(\/' . $defaultPrefix . '\/)/', $redirectUrl)) {
      if ($desiredPrefix != FALSE) {
        // Replace on the first occurence of default lang code if exits.
        return self::str_replace_first('/' . $defaultPrefix . '/', '/', $redirectUrl);
      }
    }
    // Return same Route URL when nothing matched.
    return $redirectUrl;
  }

  /**
   * OP-6885: Portal Registration - Add Registraion source in Organic Join
   * Determines the user's LifePoints access source.
   */
  public static function getDeviceSource() {
    $request = \Drupal::requestStack();
    // Get OS from deeplink services.
    $deeplinkBuildereplink = new DeeplinkBuilder($request);
    $getEnvironment = $deeplinkBuildereplink->getEnvironment();
    // Get query parameter for mobile app.
    $urlParams = \Drupal::request()->query->all();
    // If device=app available as an query parameter,
    // return mobileapp.
    if (isset($urlParams['device']) && $urlParams['device'] == 'app') {
      return 'mobileapp';
    }
    // If device query parameter not available and OS is either android or ios,
    // return mobileweb.
    elseif (!empty($getEnvironment) && (in_array($getEnvironment, ['android', 'ios']))) {
      return 'mobileweb';
    }
    // Default return web.
    else {
      return 'web';
    }
  }

  

}
