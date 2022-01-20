<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;
use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;
use Drupal\lp_lib\Util\Validation;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * WS response object for common service.
 */
class Registration {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while registering.';
  const RECRUITMENT_JOIN = 'PortalJoin';
  const API_JOIN = 'JoinAPI';
  const ORGANIC_JOIN = 'Organic';

  /**
   * The Common Service Client.
   *
   * @var Drupal\lp_lib\Util\CommonServiceClient
   */
  protected $wsClient;

  /**
   * The usersession.
   *
   * @var Drupal\lp_lib\Util\UserSession
   */
  protected $userSession;
  protected $path;
  protected $request;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Cunstructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient, $userSession, $request) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
    $this->request     = $request->getCurrentRequest();
  }

  /**
   * Call SOI registration endpoint.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function soiRegistration(array $data) {
    if (!isset($data["ip"])) {
      throw new \Exception(self::DEFAULT_ERROR_MSG);
    }
    // OP-6863 Endpoint ajax call to Register SOI Panelist.
    $response = $this->wsClient->ajaxPost('OneP-AccountServices/Panelist', $data);
    return $response;
  }

  /**
   * Auto Login to user after clicking DOI email.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function TokenLogin(array $data) {

    // Start of OP-3950.
    // Login source would be portal by defaule.
    // For Mobile sso link that should be MobileAPP hence overriding it from mobile sso controller.
    if (!isset($data['loginSource'])) {
      $config = \Drupal::service('settings');
      // Getting LoginSource from Config file.
      $loginsource = $config->get('lp')['login']['loginsource'];
      $data['loginSource'] = $loginsource;
    }
    // OP-5388 LSID for token login end points.
    if (!isset($data['lsid'])) {
      $data['lsid'] = $this->userSession->getLsid();
    }

    // OP-5904 Adding webcam parameter and user agent.
    $externalSession = $this->request->getSession();
    $panelistExternalSessions = $externalSession->get('panelistExternalSessions');
    $data['webcamAvailable'] = (isset($panelistExternalSessions['panelistSystemWebCamStatus'])) ? $panelistExternalSessions['panelistSystemWebCamStatus'] : 0;

    $data['agent'] = $_SERVER['HTTP_USER_AGENT'];

    // Check if service is initialized.
    if (!isset($this->panelConfiguration)) {
      $this->panelConfiguration = \Drupal::service('lp.configuration');
    }
    // Get current accept language.
    $acceptLanguage = $this->panelConfiguration->getAcceptLanguage();

    // Get Country code by IP.
    $countryCode = MappingUsages::getCountryIPandCode()['country_code'];

    // Get alternate panel code.
    $altAcceptLang = MappingUsages::getPanelAcceptLangListByCountryCode($countryCode, $acceptLanguage);

    if ($altAcceptLang == FALSE) {
      $data['x-alternate-accept-languages'] = NULL;
    }
    else {
      // Get available panel for same country in CSV format.
      $data['x-alternate-accept-languages'] = implode(',', $altAcceptLang);
    }

    $response = $this->wsClient->post('OneP-AccountServices/TokenLogin', $data);
    return $response;
  }

  /**
   * Activate the panelist.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function PanelistActivate(array $data) {
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/Activate', $data);
    return $response;
  }

  /**
   * Get term version by term node path.
   */
  public function getTermVersionByNodePath($nodePath) {

    /* get node details by path */
    $result = [];
    $terms_node = MappingUsages::getNodeDetailsByPath($nodePath);
    if (!empty($terms_node)) {
      $result['termType'] = $terms_node->get('field_lp_term_type')->value;
      $termVersionId = $terms_node->get('field_lp_terms_version')->target_id;

      /* get terms version taxonomy details */
      $termData = MappingUsages::getTermIdByFieldNameAndValue(
        [
          'tid' => $termVersionId,
        ],
        'lp_terms_version',
        TRUE
      );
      if (!empty($termData)) {
        $result['termVersionTitle'] = $termData->get('name')->value;
      }
    }

    return $result;
  }

  /**
   * Save preregistration data for Panelist.
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function panelistPreRegistration(array $data, $isAjax = FALSE) {
    // OP-6864 Endpoint ajax call to PanelistPreRegistration.
    if ($isAjax) {
      return $this->wsClient->ajaxPost('OneP-RecruitmentServices/PanelistPreRegistration', $data);
    }
    $response = $this->wsClient->joinApiPost('OneP-RecruitmentServices/PanelistPreRegistration', $data);
    return $response;
  }

  /**
   * Get preregistration data for Panelist.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function getPreRegistrationData(array $data) {
    $response = $this->wsClient->joinApiGet('OneP-RecruitmentServices/PanelistPreRegistration', $data);
    return $response;
  }

  /**
   * Save recruitment data.
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function recruitment(array $data, $isAjax = FALSE) {
    // OP-6864 - Endpoint ajax call with recruitment information.
    if ($isAjax) {
      return $this->wsClient->ajaxPost('OneP-RecruitmentServices/Recruitment', $data);
    }
    $response = $this->wsClient->post('OneP-RecruitmentServices/Recruitment', $data);
    return $response;
  }

  /**
   * Process recruitment data.
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function processRecuritmentData(array $data, $isAjax = FALSE) {
    // Get recruitment fields details from mapping file.
    $recruitmentField = MappingUsages::getRecruitmentFieldsList();
    foreach ($recruitmentField as $urlField => $endpointField) {
      if (!empty($data[$urlField])) {
        $recruitmentData[$endpointField] = $data[$urlField];
      }
    }
    $recruitmentData['bearer_required'] = TRUE;
    $recruitmentData['userName'] = $recruitmentData['contactEmail'];
    // $recruitmentData['transactionId'] = '1234';
    // $recruitmentData['offerID'] = '9999';.
    $recruitmentData['domain'] = MappingUsages::getPanelId();
    // OP-6864 - ajax call with recruitment information
    if ($isAjax) {
      return $this->recruitment($recruitmentData, $isAjax);
    }
    return $this->recruitment($recruitmentData);
  }

  /**
   * Process preregistration with jointype.
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function processPreRegistration(array $data, $isAjax = FALSE) {
    // Validate all the required parameter and format.
    $validation = FALSE;
    if ($data['joinType'] == self::API_JOIN) {
      // Need to validate the data coming from URL.
      $validation = Validation::validateJoinAPIParam($data);
    }
    elseif ($data['joinType'] == self::RECRUITMENT_JOIN) {
      // Fire validation TRUE because already happen once reg from loaded.
      // Parameters are coming in the url with registration form.
      $validation = TRUE;
    }
    if ($validation) {
      $preRegistrationData = [];
      // Fields with URL param and api param.
      // OP-7123: Portal: Send City and State on the Post PreRegistration API
      if ($data['joinType'] == self::RECRUITMENT_JOIN) {
        $preRegistrationFields = MappingUsages::getJoinAPIFieldsList(self::RECRUITMENT_JOIN);
      }
      else {
        $preRegistrationFields = MappingUsages::getJoinAPIFieldsList();
      }
      foreach ($preRegistrationFields as $urlField => $endpointField) {
        if (!empty($data[$urlField])) {
          $preRegistrationData[$endpointField] = $data[$urlField];
        }
      }
      $preRegistrationData['domain'] = MappingUsages::getPanelId();
      if (isset($data['locale']) & !empty($data['locale'])) {
        $preRegistrationData['locale'] = $data['locale'];
      }
      else {
        if (isset($data['lang']) && !empty($data['lang']) && isset($data['country']) && !empty($data['country'])) {
          $preRegistrationData['locale'] = strtolower($data['lang']) . '-' . strtoupper($data['country']);
        }
      }

      // OP-6885: Portal Registration - Add Registraion source in Organic Join.
      $preRegistrationData['registrationSource'] = MappingUsages::getDeviceSource();

      // Token is required for this.
      $preRegistrationData['bearer_required'] = TRUE;

      // Pass checks validation.
      $configValidationChecks = MappingUsages::getQualityValidationChecks();
      $preRegistrationData['emailValidation'] = $configValidationChecks['emailValidation'];
      $preRegistrationData['ipCheck'] = $configValidationChecks['ipCheck'];
      $preRegistrationData['addressCheck'] = $configValidationChecks['addressCheck'];
      $preRegistrationData['isFullIpCheckEnabled'] = $configValidationChecks['isFullIpCheckEnabled'];

      // Recruitment URL Logging parameters - OP-3304.
      if ($data['joinType'] == self::RECRUITMENT_JOIN) {
        $preRegistrationData['attributes']['Offer_id'] = $data['offer_id'];
        $preRegistrationData['attributes']['aff_id'] = $data['aff_id'];
        $preRegistrationData['attributes']['contact_Email'] = $data['contact_email'];
        $preRegistrationData['attributes']['EntryURL'] = $data['entryurl'];
        $preRegistrationData['attributes']['EntryURLtype'] = $data['entryurltype'];
        $preRegistrationData['attributes']['lang'] = $data['lang'];

        // OP-6776 NuDetect Drupal Registration - Portal Join Implementation.
        if (isset($data['nuDetect']) && !empty($data['nuDetect'])) {
          $preRegistrationData['nuDetect'] = $data['nuDetect'];
          $preRegistrationData['sid_nudetect'] = $data['sid_nudetect'];
          $preRegistrationData['uuid_nudetect'] = $data['uuid_nudetect'];
        }
      }

      // End of paramters for recruitmet.
      // OP-6864 - pass isAjax param when need to call ajax
      $response = $this->panelistPreRegistration($preRegistrationData, $isAjax);
      return $response;
    }
    else {
      return [];
    }
  }

  /**
   * Process Recruitment DOI.
   *
   * @return Drupal\ls_lib\WsClient\CommonServiceResponse
   */
  public function processTokenLogin(array $data) {
    // Fields with form param and api param.
    $tokenLoginFields = MappingUsages::getTokenLoginFieldsList();
    foreach ($tokenLoginFields as $formField => $endpointField) {
      if (!empty($data[$formField])) {
        $tokenLoginData[$endpointField] = $data[$formField];
      }
    }
    $response = $this->TokenLogin($tokenLoginData);
    return $response;
  }

  /**
   * PATCH request: Verity Score Update endpoint .
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function panelistUpdateVerityScore(array $data) {
    $response = $this->wsClient->patch('OneP-AccountServices/UpdateVerityScore', $data);
    return $response;
  }

  /**
   * POST request: Verity Address API, Validate DataPlus endpoint .
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   *   Endpoint is coming from Configs in verityAddressValidatePost function.
   */
  public function getVerityAddressValidateDataPlus(array $data, $type) {
    $response = $this->wsClient->verityAddressValidatePost($data, $type);
    return $response;
  }

  /**
   * Call DoiToken Endpoint.
   */
  public function doiToken(array $data) {
    // Endpoint to Register DOI Email organic link.
    $response = $this->wsClient->get('OneP-AccountServices/DoiToken', $data);
    return $response;
  }

  /**
   * Call Device Detection RIL Endpoint.
   * Capture +Perform device detection for a panelist.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function postDetectDeviceData(array $data) {
    // Endpoint to Register DOI Email Portal Join link.
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/DetectDevice', $data);
    return $response;
  }

  /**
   * Perform address check for a panelist, sets status and reason if a failed check for the panel.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function postPanelistAddressCheck(array $data) {
    // Endpoint to Register DOI Email Portal Join link.
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/AddressCheck', $data);
    return $response;
  }

  /**
   * Perform Full IP checks for a panelist, sets status and reason if a failed check for the panel.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function postPanelistFullIpCheck(array $data) {
    // Endpoint to Register DOI Email Portal Join link.
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/FullIpCheck', $data);
    return $response;
  }

  /**
   * PATCH request: Join API endpoint .
   *
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function patchPanelistEmailVerificationJoinAPI(array $data, $isAjax = FALSE) {
    // OP-6865 - ajax call for JoinAPI EmailVerification
    if ($isAjax) {
      return $this->wsClient->ajaxPatch('OneP-AccountServices/EmailVerification/JoinAPI', $data);
    }
    $response = $this->wsClient->patch('OneP-AccountServices/EmailVerification/JoinAPI', $data);
    return $response;
  }

  /**
   * Post request: Create an audit log record for a panelist verity challenge question .
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function panelistUpdateAuditLog(array $data) {
    $response = $this->wsClient->post('OneP-AccountServices/Audit/Panelist', $data);
    return $response;
  }

  /**
   * Method to perform DOI Token.
   */
  public function processDoiToken($queryPrams) {
    try {
      // Fetch parameters from DOI email link, which required for DOI endpoint.
      $domain = (isset($queryPrams['domain'])) ? $queryPrams['domain'] : NULL;
      $acceptLang = (isset($queryPrams['acceptLang'])) ? $queryPrams['acceptLang'] : NULL;
      $joinType = (isset($queryPrams['joinType'])) ? $queryPrams['joinType'] : NULL;
      $userName = (isset($queryPrams['userName'])) ? $queryPrams['userName'] : NULL;
      // If locale is set in url instead of acceptLang.
      if ($acceptLang == NULL && isset($queryPrams['locale'])) {
        $acceptLang = $queryPrams['locale'];
      }
      $token = (isset($queryPrams['token'])) ? $queryPrams['token'] : NULL;
      $addressCheck = isset($queryPrams['addressCheck']) ? $queryPrams['addressCheck'] : NULL;

      $correctPassback = FALSE;
      if (isset($domain) && $domain != NULL) {
        if (isset($acceptLang) && $acceptLang != NULL) {
          if (isset($token) && $token != NULL) {
            $correctPassback = TRUE;
          }
        }
      }
      // Validating URL for correct query parameters.
      if ($correctPassback) {
        // Validate panelist is connected to same panel.
        if (!isset($this->panelConfiguration)) {
          $this->panelConfiguration = \Drupal::service('lp.configuration');
        }

        // OP-6571 no need to remove this check as url panel will be set by get_panel_code.
        if (strtolower($this->panelConfiguration->getAcceptLanguage()) != strtolower($acceptLang)) {
          // OP-5717 - log entry when connect to the wrong panel.
          $paramAcceptLang = $acceptLang;
          $connectedAcceptLang = $this->panelConfiguration->getAcceptLanguage();
          $this->addLocaleLogs($userName, $joinType, $paramAcceptLang, $connectedAcceptLang);
          // \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping("err_general_message_common"));
          // OP-5605 - Moving error message to Login page instead of Home page
          $loginQueryParams = [];
          $loginQueryParams['re'] = 'err_gen';
          $redirectResponse = \Drupal::service('redirect_response');
          // Call to Recreate URL when Default EN lang come in URL or as default Panel.
          $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_login.Login', $loginQueryParams));
          $redirectResponse->send();
          return [
            '#cache' => ["max-age" => 0],
          ];
        }

        if ($queryPrams['joinType'] == self::API_JOIN) {
          // Forcelfully changing 'addressCheck' value to FALSE;
          // In order to execute '/Panelist/AddressCheck' endpoint at the end.
          // OP-2664.
          $addressCheck = 'FALSE';
        }
        else {
          // Getting value from quality validation checks.
          $overrideValidationChecks = MappingUsages::getQualityValidationChecks(FALSE);
          // Set AddressCheck as per override locale OP-4225.
          if (isset($overrideValidationChecks['addressCheck']) && $overrideValidationChecks['addressCheck'] != '') {
            $addressCheck = $overrideValidationChecks['addressCheck'];
          }
          // Set AddressCheck value from DOI url OP-4225.
          elseif (isset($addressCheck) && $addressCheck != '') {
            $addressCheck = $addressCheck;
          }
          else {
            // Set addressCheck value from setting,
            // if addressCheck not set in DOI url.
            $config = \Drupal::service('settings');
            $configValidationChecks = $config->get('lp')['validation_checks'];
            $addressCheck = $configValidationChecks['addressCheck'];
          }
        }
        $data = [];
        $data['domain'] = MappingUsages::getPanelId();
        $data['acceptLang'] = $acceptLang;
        $data['token'] = $token;
        $data['addressCheck'] = $addressCheck;
        // To capture Device Details.
        $data['ip'] = MappingUsages::get_client_ip_address();
        $data['agent'] = $_SERVER['HTTP_USER_AGENT'];
        $queryParams = [];
        // Call DoiToken endpoint with URL token.
        $response = $this->doiToken($data);
        if (!empty($response)) {
          // No Redirection for JoinApi.
          if ($queryPrams['joinType'] == self::API_JOIN) {
            return $response;
          }
          if (is_object($response) && isset($response->data()['token'])) {
            $queryParams['token'] = $response->data()['token'];
            // OP-5343 : Passing jointype to confirmation page.
            $queryParams['joinType'] = $joinType;
          }
          else {
            // \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping("err_general_message_common"));
            // OP-5605 - Moving error message to Login page instead of Home page
            $loginQueryParams = [];
            $loginQueryParams['re'] = 'err_gen';
            $redirectResponse = \Drupal::service('redirect_response');
            // Call to Recreate URL when Default EN lang come in URL or as default Panel.
            $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_login.Login', $loginQueryParams));
            $redirectResponse->send();
            return [
              '#cache' => ['max-age' => 0],
            ];
          }

          // Get the register confirmation link with Doi token.
          $redirectResponse = \Drupal::service('redirect_response');
          // Call to Recreate URL when Default EN lang come in URL or as default Panel.
          $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_registration.triggerDoi', $queryParams));
          $redirectResponse->send();
          return [
            '#cache' => ['max-age' => 0],
          ];
        }
        else {
          // OP- 4943: Allow panelist to retry challenge question only once.
          // Logout panelist if session exist and user navigates to DOI-By-Email page.
          $this->userSession->panelistLogout();
          // If empty response.
          return [];
        }
      }
      else {
        \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping("err_general_message_common"));
      }
    }
    catch (\Exception $e) {
      MappingUsages::exceptionLogInfo($e, $data);
      \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping("something_went_wrong"));
    }
  }

  /**
   * Logging for match with connected locale.
   *
   * @param: [string] user name
   * @param: [string] type
   * @param: [string] param accept lang
   * @param: [string] connected accept lang
   */
  public static function addLocaleLogs($userName = NULL, $joinType = NULL, $paramAcceptLang = NULL, $connectedAcceptLang = NULL) {
    // OP-5717 - log entry when connect to the wrong panel.
    $logMsg = '<pre><code>';
    if ($userName != NULL) {
      $logMsg .= 'userName: ' . $userName;
    }
    if ($joinType != NULL) {
      $logMsg .= ' JoinType: ' . $joinType;
    }
    $logMsg .= ', Locale from parameter ' . strtolower($paramAcceptLang) . ' not match with connected locale ' . strtolower($connectedAcceptLang);
    $logMsg .= '</code></pre>';

    \Drupal::logger('lp_behavior')->info($logMsg);
  }

  /**
   * Get PanelistData details by Email address.
   */
  public function getPanelistDataByEmail(array $data)
  {
    $response = $this->wsClient->joinApiGet('OneP-AccountServices/PanelistData', $data);
    return $response;
  }

}
