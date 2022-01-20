<?php

namespace Drupal\lp_lib\WsClient;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GuzzleHttp\Exception\ClientException;
use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;
use Drupal\Core\Url;

/**
 *
 */
class CommonServiceClient extends WsClientAbstract {

  protected $wsConfig;
  private $host;
  private $acceptResponse;
  private $acceptLanguage;
  private $panelConfiguration;

  /**
   * @param $httpClient
   *   - GuzzleHttp\Client
   * @param $config
   *   - Drupal\Core\Config\ConfigFactory (sites/default/settings.php)
   */
  public function __construct($config, $httpClient, $panelConfiguration, $userSession) {
    // @todo get the settings from settings.php.
    parent::__construct($config, $httpClient, $panelConfiguration);
    $this->wsConfig = $this->config->get('lp')['common_service'];
    $this->panelConfiguration = $panelConfiguration;
    $this->userSession = $userSession;
  }

  /**
   * Function to get the host url.
   */
  protected function setHostWithAcceptResponse($endpoint, $method = NULL) {
    switch ($endpoint) {
      case 'Jade-UsermanagementServices/authenticate':
      case 'OneP-AccountServices/Panelist/Activate':
      case 'OneP-AccountServices/TokenLogin':
      case 'OneP-AccountServices/ResendDoi':
      case 'OneP-AccountServices/Panelist/DetectDevice':
      case 'OneP-AccountServices/Panelist/AddressCheck':
      case 'OneP-AccountServices/Audit/Panelist':
        $this->acceptResponse = 'application/vnd.lsr.app.portal-2.0+json;charset=UTF-8';
        break;

      // Check the endpoint is called from GET method.
      case 'OneP-AccountServices/Panelist':
        if ($method == 'GET') {
          $this->acceptResponse = 'application/vnd.lsr.app-2.0+json;charset=UTF-8';
        }
        else {
          $this->acceptResponse = 'application/vnd.lsr.app.portal-2.0+json;charset=UTF-8';
        }
        break;

      case 'OneP-AccountServices/AllProperties':
      case 'OneP-SurveyServices/SurveyOpportunities':
      case 'OneP-Survey/Survey/Start':
      case 'OneP-AccountServices/PanelistInfo':
      case 'OneP-SecurityServices/User/ChangeAccount':
      case 'OneP-SecurityServices/User':
      case 'OneP-AccountServices/Panelist/ForgotPassword':
      case 'OneP-IncentiveServices/PerksLogin':
      case 'OneP-RecruitmentServices/PanelistPreRegistration':
      case 'OneP-RecruitmentServices/Recruitment':
      case 'OneP-RecruitmentServices/EventReward':
      case 'OneP-AccountServices/Panelist/ChangeAccount/Verification':
      case 'OneP-IncentiveServices/Transactions':
      case 'OneP-AccountServices/UpdateVerityScore':
      case 'OneP-AccountServices/Panelist/FullIpCheck':
      case 'OneP-AccountServices/Panelist/ChangePassword':
      case 'OneP-AccountServices/DoiToken':
      case 'OneP-AccountServices/PanelistData':
      case 'OneP-AccountServices/EmailVerification/JoinAPI':
        $this->acceptResponse = 'application/vnd.lsr.app-2.0+json;charset=UTF-8';
        break;

      case 'OneP-Bridge/ZendeskUrl/HelpCenter/PanelistId':
      case 'SaveSSQOffer':
      case 'SaveSSQResponse':
      case 'OneP-Survey/Statistics/PanelistId':
        $this->acceptResponse = 'application/json';
        break;

      default:
        $this->acceptResponse = 'application/vnd.lsr.app.portal-2.0+json;charset=UTF-8';
        break;
    }

    $type = explode("/", $endpoint);
    switch ($type[0]) {
      case 'OneP-AccountServices':
        $this->host = $this->wsConfig['OneP-AccountServices-Host'];
        break;

      case 'OneP-SecurityServices':
        $this->host = $this->wsConfig['OneP-SecurityServices-Host'];
        break;

      case 'OneP-Bridge':
        $this->host = $this->wsConfig['OneP-BridgeServices-Host'];
        break;

      case 'KantarConsent':
        $this->host = $this->wsConfig['OneP-Consent-Host'];
        break;

      case 'OneP-IncentiveServices':
        $this->host = $this->wsConfig['OneP-IncentiveServices-Host'];
        break;

      case 'OneP-RecruitmentServices':
        $this->host = $this->wsConfig['OneP-RecruitmentServices-Host'];
        break;

      case 'OneP-Survey':
        $this->host = $this->wsConfig['OneP-SurveyServices-Host'];
        break;

      case 'OneP-Cookie':
        $this->host = $this->wsConfig['OneP-CookieServices-Host'];
        break;

      case 'ingest':
        $this->host = $this->wsConfig['OneP-Firehose-Host'];
        break;

      case 'next-questions':
        $this->host = $this->wsConfig['OneP-Api-CuriosityIR-Host'];
        break;

      case 'questions':
      case 'answer':
        $this->host = $this->wsConfig['OneP-Api-CuriosityCore-Host'];
        break;

      case 'SaveSSQOffer':
      case 'SaveSSQResponse':
        $this->host = $this->wsConfig['SSQ-Host'];
        break;

      default:
        // Throw Exception.
        break;
    }
  }

  /**
   * Method to unset the nucaptcha html from log.
   */
  protected function unsetNuCaptchaForLog($wsResponse) {

    $responseData['httpStatus'] = $wsResponse->httpStatus();
    $responseData['httpMsg'] = $wsResponse->httpMsg();
    $responseData['data'] = $wsResponse->data();

    // Truncate the widget code in log for nudetect.
    if (isset($responseData['data']['nudetect']['captchaHtml']) && !empty($responseData['data']['nudetect']['captchaHtml'])) {
      // Unset nucaptcha html for log as its too long.
      $responseData['data']['nudetect']['captchaHtml'] = 'Captcha is unset for log';
      return $responseData;
    }
    else if (isset($responseData['data']['nuDetect']['captchaHtml']) && !empty($responseData['data']['nuDetect']['captchaHtml'])) {
      // Unset nucaptcha html for log as its too long.
      $responseData['data']['nuDetect']['captchaHtml'] = 'Captcha is unset for log';
      return $responseData;
    }
    else {
      return $wsResponse;
    }
  }

  /**
   * Process the exception flow.
   * Log the exception then handle that.
   */
  protected function processExceptionFlow($method = NULL, $url = NULL, $data = NULL, $headers = NULL, $endpoint = NULL, $e = NULL, $type = NULL, $logName = NULL) {
    // Log the exception data.
    switch ($type) {
      case 'ClientException':
        // Truncate log for nucaptcha html.
        $response = $e->getResponse();

        $additionalData = json_decode($e->getResponse()->getBody(), TRUE);
        if (
          $endpoint == 'http://jade-usrmgmnt-lb-1497249384.us-west-2.elb.amazonaws.com/Jade-UsermanagementServices/authenticate' ||
          $endpoint == 'OneP-AccountServices/Panelist' ||
          $endpoint == 'OneP-RecruitmentServices/PanelistPreRegistration' ||
          $endpoint == 'OneP-AccountServices/EmailVerification/JoinAPI' ||
          $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword' ||
          $endpoint == 'OneP-AccountServices/Panelist/ChangePassword'
        ) {
          // Create a object of common service response.
          $wsResponse = new CommonServiceResponse();
          // Set the wsresponse from response object.
          $wsResponse->setResponse($response);
          $responseData = $this->unsetNuCaptchaForLog($wsResponse);
          // Truncate the widget code in log for nudetect.
          if (is_array($responseData) && isset($responseData['data']['nuDetect']['captchaHtml']) && !empty($responseData['data']['nuDetect']['captchaHtml'])) {
            // Unset the full RIL response as its too long with nucaptcha response.
            $additionalData = '';
          }
        }
        else {
          $responseData = $response;
        }

        $this->logData($method, $url, $data, $headers, $responseData, $additionalData, $type, $logName);
        // Checking is coming from Eligibility call or not.
        if ($method == 'GET' && isset($data['userName']) && $data['isEligible'] == 'TRUE') {
          $this->handleJoinApiException($e, 'ClientException', 'PortalJoin');
        }
        break;

      case 'RequestException':
        if ($e->hasResponse()) {
          $this->logData($method, $url, $data, $headers, $e->getResponse(), json_decode($e->getResponse()->getBody(), TRUE), $type, $logName);
        }
        else {
          $this->logData($method, $url, $data, $headers, $e->getMessage(), '', $type, $logName);
        }
        break;

      default:
        $this->logData($method, $url, $data, $headers, $e->getResponse(), '', $type, $logName);
        break;

    }

    // Handle the exception.
    // If recruitment service call for join api show the custom error message.
    if (isset($data['ajaxcall'])) {
      return $this->handleAjaxException($e, $type, $endpoint);
    }
    elseif ($endpoint == 'OneP-RecruitmentServices/Recruitment' && \Drupal::request()->query->get('__func') == 'api') {
      return $this->handleJoinApiException($e, $type, 'JoinAPI');
    }
    else {
      $this->handleException($e, $type, $endpoint);
    }
  }

  /**
   * Log request parameter.
   */
  protected function logData($method = NULL, $url = NULL, array $data = [], array $headers = [], $response = NULL, $additionalData = NULL, $type = NULL, $logName = NULL) {
    $userName = NULL;

    if (isset($data['userName']) && !empty($data['userName'])) {
      $userName = $data['userName'] . ' - ';
    }
    // Extract user email from session.
    elseif ($this->userSession->isAuthenticated()) {
      $panelistSessionData = $this->userSession->getPanelistSessionData();
      $userName = $panelistSessionData['emailAddress'] . ' - ';
    }
    $logMsg = '<pre><code>' . $userName . 'Method: ' . $method . ' Request </br>Endpoint: ' . $url . '</br>';

    if (!empty($data)) {
      // OP-5701 Changing/replacing password field content data for Patch request.
      foreach ($data as $dataKey => $dataValue) {
        // For patch request.  [0] => Array ([name] => password, [contents] => 'password').
        if (isset($dataValue['name']) &&
          $dataValue['name'] == 'password' &&
          isset($dataValue['contents'])
        ) {
          $data[$dataKey]['contents'] = '*******';
        }
        elseif (preg_match("/password/i", $dataKey) === 1) {
          $data[$dataKey] = '*******';
        }
      }

      // Truncate the widget code in log for nudetect.
      if (isset($data['nuDetect']) && !empty($data['nuDetect'])) {
        $nuData = (array) json_decode($data['nuDetect']);
        if (isset($nuData['widgetData']) && strlen($nuData['widgetData']) > 100) {
          $nuData['widgetData'] = substr($nuData['widgetData'], 0, 100) . "...truncated to 100 char.";
        }
        $data['nuDetect'] = json_encode($nuData);
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
      if (isset($logName) && !empty($logName)) {
        \Drupal::logger($logName)->error($logMsg);
      }
      else {
        \Drupal::logger('LifePoints Portal')->error($logMsg);
      }
    }
    else {
      if (isset($logName) && !empty($logName)) {
        \Drupal::logger($logName)->debug($logMsg);
      }
      else {
        \Drupal::logger('LifePoints Portal')->debug($logMsg);
      }
    }
  }

  /**
   * Function to set Accept Language.
   */
  protected function setAcceptLanguage() {
    $acceptLanguage = $this->panelConfiguration->getAcceptLanguage();
    if (!empty($acceptLanguage)) {
      $this->acceptLanguage = $acceptLanguage;
      return;
    }

    // If above if condition not true.
    \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
    $frontURL = Url::fromRoute('lp_login.Logout');
    $redirectResponse = \Drupal::service('redirect_response');
    $redirectResponse->setTargetUrl($frontURL->toString());
    $redirectResponse->send();
    exit;
  }

  /**
   * Unset the quality checks data if already set in headers.
   */
  protected function unsetQualityChecksFromData(&$data) {
    // Header set already so remove the ipchecks from data.
    foreach (['emailValidation', 'ipCheck', 'isFullIpCheckEnabled', 'maxIpCapCheck', 'smartScreenChecks'] as $key) {
      if (isset($data[$key])) {
        unset($data[$key]);
      }
    }
  }

  /**
   * Perform the business login to call get request.
   */
  protected function getRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint, 'GET');
    $this->setAcceptLanguage();
    // Gernrating the API URL host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    // Check the options if we need to pass params in URL.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }
    try {
      // Modify GET header based on Endpoint Prefix.
      $type = explode("/", $endpoint);
      switch ($type[0]) {
        case 'KantarConsent':
          $headers = $this->setHeader('UCMget', $data);
          break;

        case 'OneP-Bridge';
          $headers = $this->setHeader('gendeskurlget', $data);
          break;

        case 'next-questions':
          $headers = $this->setHeader('curiosityIRCheckGet', $data);
          break;

        case 'questions':
          $headers = $this->setHeader('curiosityCoreGet', $data);
          break;

        // OP-5407 reg2 event reward points.
        case 'OneP-RecruitmentServices':
          $headers = $this->setHeader('eventreward', $data);
          break;

        // OP-5792 return same header for survey start.
        case 'OneP-Survey':
          $headers = $this->setHeader('surveyUrlGet', $data);
          break;

        default:
          $headers = $this->setHeader('get', $data);
          break;
      }
      // Notify the logs when Host's X API key is not found.
      if (isset($headers['x-api-key']) && $headers['x-api-key'] == NULL) {
        $logMsg = '<pre>' . t('Sorry! X-API-Key is not found for Get Endpoint') . ' - ' . $url;
        \Drupal::logger('lp_exception')->error($logMsg);
      }
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      // Call the api and pass the required parameter.
      $response = \Drupal::httpClient()->get($url . '?' . http_build_query($data),
      [
        'headers' => $headers,
      ]);
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $type = explode("/", $endpoint);
      if ($type[0] == 'questions') {
        $wsResponse->setResponse($response, 'curiosityCoreGet');
      }
      else {
        $wsResponse->setResponse($response);
      }
      // Log response.
      $this->logData('GET', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Function to handle the exceptions.
   */
  protected function handleJoinApiException(\Exception $e, $type, $joinType = 'PortalJoin') {
    switch ($type) {
      case 'ClientException':
        $wsResponse = new CommonServiceResponse();
        $wsResponse->setResponse($e->getResponse());
        switch ($wsResponse->httpStatus()) {
          case '201':
          case '200':
            if ($joinType == 'JoinAPI') {
              $error_msg = CommonMessenger::errorMessageMapping('join_api_success');
              print $error_msg;
              exit();
            }
            else {
              // Not require for Portal Join.
            }
            break;

          case '409':
            if ($joinType == 'JoinAPI') {
              // OP-5666 409 conflict case.
              $errorData = $wsResponse->data();
              if (isset($errorData['type']) && $errorData['type'] == 'ERR_DUPLICATE_HO_TRANSACTION_ID') {
                $error_msg = CommonMessenger::errorMessageMapping("err_duplicate_ho_transaction_id");
                print '101: ' . $error_msg;
              }
              else {
                // OP-6769.
                $error_msg = CommonMessenger::errorMessageMapping('join_api_duplicate_error_new');
                print $error_msg;
                print '<br>';
                print 'Code: 101 Duplicate Panelist';
              }
              exit();
            }
            else {
              \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping('user_exist'));
              return;
            }
            break;

          case '400':
            if ($joinType == 'JoinAPI') {
              // OP-6435.
              $errorData = $wsResponse->data();
              $word = "contactEmail:  may not be empty";
              if (isset($errorData['type']) && $errorData['type'] == 'ERR_INVALID_EMAIL' && strpos($errorData['message'], $word) !== FALSE) {
                $error_msg = CommonMessenger::errorMessageMapping("email_empty_joinapi");
                print $error_msg;
                print '<br>';
                print 'Code: 104 Invalid Email';
              }
              else {
                // OP-6769.
                $error_msg = CommonMessenger::errorMessageMapping('join_api_missing_param_error_new');
                print $error_msg;
                print '<br>';
                print 'Code: 103 Invalid/Missing Parameters';
              }
              exit();
            }
            else {
              if ($joinType == 'PortalJoin') {
                $error_msg = CommonMessenger::errorMessageMapping('join_api_invalid_email_error');
                print '104: ' . $error_msg;
                exit();
              }
            }
            break;

          case '406':
            if ($joinType == 'JoinAPI') {
              $error_msg = CommonMessenger::errorMessageMapping('join_api_invalid_email_error');
              print '104: ' . $error_msg;
              exit();
            }
            else {
              // Not require for Portal Join.
            }
            break;

          case '422':
            if ($joinType == 'JoinAPI') {
              $errorData = $wsResponse->data();
              $cintMessgae = $errorData['message'];
              // Check the message is for duplicate panelist.
              if (strpos($cintMessgae, 'email_address') !== FALSE &&
              strpos($cintMessgae, 'has already been taken') !== FALSE
              ) {
                // OP-6769.
                $error_msg = CommonMessenger::errorMessageMapping('join_api_duplicate_error_new');
                print $error_msg;
                print '<br>';
                print 'Code: 101 Duplicate Panelist';
                exit();
              }
              else {
                // OP-6769.
                $error_msg = CommonMessenger::errorMessageMapping('join_api_missing_param_error_new');
                print $error_msg;
                print '<br>';
                print 'Code: 103 Invalid/Missing Parameters';
              }
            }
            else {
              // Not require for Portal Join.
            }
            break;

          // OP-7054 Eligible For Recruitment Join.
          case '404':
            if ($joinType == 'PortalJoin') {
              // OP-7055. No need to show details message. Only "OK" is Fine.
              print 'OK';
              //print 'Code: 404 Potential Panelist';
              exit();
            }
            break;

          default:
            $error_msg = empty($wsResponse->errorMsg()) ? $wsResponse->httpMsg() : $wsResponse->errorMsg();
            if ($joinType == 'JoinAPI') {
              print $wsResponse->httpStatus() . ': ' . $error_msg;
            }
            else {
              \Drupal::messenger()->addError($error_msg);
              return;
            }
            break;
        }
        break;

      case 'RequestException':
        if ($e->hasResponse()) {
          $wsResponse = new CommonServiceResponse();
          $wsResponse->setResponse($e->getResponse());
          // $error_msg = empty($wsResponse->errorMsg()) ? $wsResponse->httpMsg() : $wsResponse->errorMsg();
          // Server Timeout.
          $error_msg = CommonMessenger::errorMessageMapping("server_timeout");
          if ($joinType == 'JoinAPI') {
            print $wsResponse->httpStatus() . ': ' . $error_msg;
            exit();
          }
          else {
            \Drupal::messenger()->addError($error_msg);
            return;
          }
        }
        break;

      case 'ConnectException':
        if ($e->hasResponse()) {
          $wsResponse = new CommonServiceResponse();
          $wsResponse->setResponse($e->getResponse());
          $error_msg = empty($wsResponse->errorMsg()) ? $wsResponse->httpMsg() : $wsResponse->errorMsg();
          if ($joinType == 'JoinAPI') {
            print $wsResponse->httpStatus() . ': ' . $error_msg;
            exit();
          }
          else {
            \Drupal::messenger()->addError($error_msg);
            return;
          }
        }
        break;
    }
  }

  /**
   * Function to handle the exceptions.
   */
  protected function handleException(\Exception $e, $type, $endpoint = NULL) {
    switch ($type) {
      case 'ClientException':
        $wsResponse = new CommonServiceResponse();
        $wsResponse->setResponse($e->getResponse(), $endpoint);

        // Product Tour.
        // Special case to hide error or warning message for Panelist in case 404 HTTP Status.
        // Anyway Logs will saved into system.
        if ($wsResponse->httpStatus() == '404' && stristr('OneP-Survey/Statistics/PanelistId', $endpoint)) {
          // This will skip Drupal Messenger function to display error/warning.
          break;
        }
        // Adding warning message : Op-4682.
        if (!empty($wsResponse->warningMsg())) {
          \Drupal::messenger()->addWarning($wsResponse->warningMsg());
        }
        else {
          \Drupal::messenger()->addError($wsResponse->errorMsg());
        }
        break;

      case 'RequestException':
        // Removed frontend message for curiosity response time out case.
        $type = explode("/", $endpoint);
        $expectedCuriosityCase = ['next-questions', 'questions'];
        if (!in_array($type[0], $expectedCuriosityCase)) {
          \Drupal::messenger()->addError(CommonMessenger::errorMessageMapping("server_timeout"));
        }
        break;
    }
  }

  /**
   * Function to handle the exceptions for ajax calls.
   */
  protected function handleAjaxException(\Exception $e, $type, $endpoint = NULL) {

    $errors = [];
    $errors['errorStatus'] = TRUE;
    switch ($type) {
      case 'ClientException':
        $wsResponse = new CommonServiceResponse();
        $wsResponse->setResponse($e->getResponse(), $endpoint);
        $errors['responseCode'] = $wsResponse->httpStatus();
        $errors['ajaxData'] = $wsResponse->data();
        $errors['message'] = $wsResponse->errorMsg();
        break;

      case 'RequestException':
        if ($e->hasResponse()) {
          $wsResponse = new CommonServiceResponse();
          $wsResponse->setResponse($e->getResponse());
          $errors['message'] = $wsResponse->httpStatus();
          $errors['message'] = CommonMessenger::errorMessageMapping("server_timeout");
        }
        else {
          $errors['responseCode'] = $e->getCode();
          $errors['message'] = CommonMessenger::errorMessageMapping("server_timeout");
        }
        break;

      default:
        $wsResponse = new CommonServiceResponse();
        $wsResponse->setResponse($e->getResponse());
        $errors['responseCode'] = $wsResponse->httpStatus();
        $errors['message'] = $wsResponse->errorMsg();
        break;
    }

    return $errors;
  }

  /**
   * Method to Set endpoint url.
   */
  protected function setEndpointUrl($endpoint) {
    if (isset($this->wsConfig['version']) && isset($this->wsConfig['version'][$endpoint])) {
      // Generating the API URL. host/version_endpoint.
      return $this->host . '/' . $this->wsConfig['version'][$endpoint];
    }

    // Generating the API URL. host/endpoint.
    return $this->host . '/' . $endpoint;
  }

  /**
   * Function to set header.
   */
  protected function setHeader($type, &$data, $endpoint = NULL) {
    $lpConfigSecrets = $this->config->get('lp')['secrets'];
    $config_settings = \Drupal::service('settings');
    switch ($type) {
      case 'unsubscribeFromEmail':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Bridge_X_Api_Key'],
        ];
        break;

      case 'post':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/x-www-form-urlencoded',
          'accept-language' => $this->acceptLanguage,
        ];
        // OP-5904 pass the useragent in header.
        // OP-6137. Not to move agent from Query to header in case of Detect device.
        if (isset($data['agent']) && isset($endpoint) && $endpoint != 'OneP-AccountServices/Panelist/DetectDevice') {
          $header['x-agent'] = $data['agent'];
          unset($data['agent']);
        }
        // OP-5987 - pass the IP Address for max calls.
        if (isset($data['maxIpCapCheck'])) {
          $header['x-onep-ip-cap-check-enabled'] = $data['maxIpCapCheck'];
        }
        break;

      case 'get':
      case 'patch':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
        ];
        // OP-6925 - Pass headers for JoinAPI.
        if ($endpoint == 'OneP-AccountServices/EmailVerification/JoinAPI') {
          $header['x-onep-domain'] = $config_settings->get('lp')['panelId'];
          // AcceptLang is the header param.
          $header['acceptLang'] = $this->acceptLanguage;
          if (isset($data['addressCheck'])) {
            $header['x-onep-address-check'] = $data['addressCheck'];
            unset($data['addressCheck']);
          }
          unset($header['accept-language']);
        }
        break;

      case 'eventreward':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-onep-domain' => $config_settings->get('lp')['panelId'],
        ];
        break;

      case 'profileupdate':
        $header = [
          'Accept' => "application/vnd.lsr.app-2.0+json;charset=UTF-8",
          'accept-language' => $this->acceptLanguage,
          'Content-Length' => 0,
        ];
        break;

      case 'delete':
        $header = [
          'Accept' => "application/vnd.lsr.app-2.0+json;charset=UTF-8",
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
        ];
        break;

      case 'gendeskurlget':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Bridge_X_Api_Key'],
        ];
        break;

      case 'gendeskurlpost':
        $header = [
          'Content-type' => 'application/x-www-form-urlencoded',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Bridge_X_Api_Key'],
        ];
        break;

      // Survey API Get & Post Headers.
      case 'surveyUrlGet':
        $header = [
          'Content-type' => 'application/x-www-form-urlencoded',
          'accept-language' => $this->acceptLanguage,
        // 'User-Agent' => $data['agent'],
          'x-api-key' => $lpConfigSecrets['Survey_X_Api_Key'],
        ];

        // For Get Survey Priority study list endpoint.
        if (isset($data['agent'])) {
          $header['User-Agent'] = $data['agent'];
        }
        // OP-6358.
        if (isset($data['x-disable-smartscreen'])) {
          $header['x-disable-smartscreen'] = 1;
          unset($data['x-disable-smartscreen']);
        }
        break;

      // Survey Finish - For adobe analytics from server side.
      case 'surveyFinishGet':
        $header = [
          'User-Agent' => $data['agent'],
          'X-Forwarded-For' => $data['ip'],
          'Accept' => '*/*',
        ];
        break;

      case 'surveyUrlPut':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Survey_X_Api_Key'],
        ];
        break;

      case 'cookieUrlGet':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Cookie_X_Api_Key'],
        ];
        break;

      case 'cookieUrlPost':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Cookie_X_Api_Key'],
        ];
        break;

      case 'recruitmentPost':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/x-www-form-urlencoded',
        ];
        break;

      case 'curiosityIRCheckGet':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Curiosity_IR_X_Api_Key'],
        ];
        break;

      case 'curiosityCorePut':
        $header = [
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Curiosity_Core_X_Api_Key'],
        ];
        break;

      case 'curiosityCoreGet':
        $header = [
          'accept-language' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Curiosity_Core_X_Api_Key'],
        ];
        break;

      // UCM - One Trust headers.
      case 'UCMget':
        $header = [
          'Content-type' => 'application/json',
          'x-api-key' => $lpConfigSecrets['Consent_X_Api_Key'],
        ];
        // OP-4536 To add backfillWithConsent = TRUE in GET method, if there is Panelist in endpoint URL.
        if (isset($data['backfillWithConsent'])) {
          if ($data['backfillWithConsent'] == 'true') {
            $header['backfillWithConsent'] = $data['backfillWithConsent'];
          }
          unset($data['backfillWithConsent']);
        }
        // Passing locale from data.
        if (isset($data['locale'])) {
          $header['locale'] = $data['locale'];
          unset($data['locale']);
        }
        break;

      case 'UCMpost':
        $header = [
          'Content-type' => 'application/json',
          'locale' => $this->acceptLanguage,
          'x-api-key' => $lpConfigSecrets['Consent_X_Api_Key'],
        ];
        break;

      // OP-4436.
      case 'Firehosepost':
        $header = [
          'Content-type' => 'application/json',
          'x-api-key' => $lpConfigSecrets['Firehose_X_Api_Key'],
        ];
        break;

      // OP-4580.
      case 'changePassword':
      case 'ForgotPassword':
        $header = [
          'Accept' => $this->acceptResponse,
          'accept-language' => $this->acceptLanguage,
          'x-onep-domain' => $config_settings->get('lp')['panelId'],
        ];
        break;

      // OP-4526.
      case 'VerityChallengeQuestionAuditLog':
        $header = [
          'Accept' => $this->acceptResponse,
          'accept-language' => $this->acceptLanguage,
          'x-onep-domain' => MappingUsages::getPanelId(),
        ];
        break;

      // OP-6070: LP - Consume API for Portal Floating Action Button (FAB) Feature 57.
      case 'FloatingActionButton':
        $header = [
          'Content-Type' => 'application/json;charset=UTF-8',
          'x-api-key' => $lpConfigSecrets['Bridge_X_Api_Key'],
        ];
        break;

      // OP-5630.
      case 'SSQ':
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => $this->acceptResponse,
          'x-api-key' => $lpConfigSecrets['SSQ_X_Api_Key'],
        ];
        break;

      default:
        $header = [
          'Accept' => $this->acceptResponse,
          'Content-type' => 'application/json',
          'accept-language' => $this->acceptLanguage,
        ];
        break;

    }
    // OP-5161 Adding Login SessionId(LSID).
    if (isset($data['lsid']) && $type != 'SSQ') {
      $header['x-lsid'] = $data['lsid'];
      unset($data['lsid']);
    }
    // OP-3129, OP-4466.
    // For Alternative Accept lang for Login Endpoint.
    if (isset($data['x-alternate-accept-languages']) && $data['x-alternate-accept-languages'] != NULL) {
      $header['x-alternate-accept-languages'] = $data['x-alternate-accept-languages'];
      // Pass alternate accept language to json data.
      $data['alternateAcceptLangs'] = $data['x-alternate-accept-languages'];
      unset($data['x-alternate-accept-languages']);
    }

    $header['domain'] = MappingUsages::getPanelId();
    if ((isset($data['token']) && !empty($data['token'])) || (isset($data['bearer_required']) && !empty($data['bearer_required']))) {
      // If bearer requried true that means user is not logged in but need authentication for endpoint.
      if ((isset($data['bearer_required']) && !empty($data['bearer_required']))) {
        unset($data['bearer_required']);
        $config = \Drupal::service('settings');

        // Getting setting from Config file.
        $bearer_authentication_details = $config->get('lp')['bearer_authentication'];
        $bearer_data = [];
        $bearer_data['userName'] = $bearer_authentication_details['username'];
        $bearer_data['password'] = $bearer_authentication_details['password'];

        $bearer_header['domain'] = $bearer_authentication_details['domain'];
        $bearer_header['Content-Type'] = $bearer_authentication_details['Content-Type'];
        $bearer_header['Accept'] = $bearer_authentication_details['Accept'];
        $url = $bearer_authentication_details['endpoint'];
        $endpoint = $bearer_authentication_details['endpoint'];

        try {
          $response = \Drupal::httpClient()->post($url,
          [
            'form_params' => $bearer_data,
            'headers' => $bearer_header,
          ]);
          // OP-5916 - Added username/emil in logs for ext/token.
          $emailId = " ";
          if (isset($data['userName'])) {
            $emailId = $data['userName'];
          }
          $bearer_data['email'] = isset($data['email']) ? $data['email'] : $emailId;

          // Create a object of common service response.
          $wsResponse = new CommonServiceResponse();
          // Set the response.
          $wsResponse->setResponse($response);
          $bearer_response = $wsResponse->data();
          $header['Authorization'] = 'Bearer ' . $bearer_response['access_token'];
          // OP-5916 - logs added for ext/token.
          $this->logData('POST', $url, $bearer_data, $bearer_header, $wsResponse);
        }
        catch (ClientException  $e) {
          // Process the exception flow.
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'ClientException');
          // Throw new \Exception($wsResponse->httpMsg());
        }
        catch (ClientErrorResponseException $e) {
          /* To-do: Client error response exception handling */
          // Process the exception flow.
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'ClientErrorResponseException');
        }
        catch (ServerErrorResponseException $e) {
          /* To-do: Server error response exception handling */
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'ServerErrorResponseException');
        }
        catch (BadResponseException $e) {
          /* To-do: Bad response exception handling */
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'BadResponseException');
        }
        catch (RequestException $e) {
          /* To-do: Any Request exception handling */
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'RequestException');
        }
        catch (ConnectException $e) {
          /* To-do: Connect exception handling */
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'ConnectException');
        }
        catch (Exception $e) {
          /* To-do: Normal exception handling */
          $this->processExceptionFlow('POST', $url, $bearer_data, $bearer_header, $endpoint, $e, 'Exception');
        }
      }
      else {
        $header['Authorization'] = 'Bearer ' . $data['token'];
      }
    }

    $activePanel = MappingUsages::get_panel_code();
    $configValidationChecks = $this->config->get('lp')['validation_checks'];
    $logMsg = "setHeader validation_checks config: " . print_r($configValidationChecks, TRUE);

    foreach (['emailValidation', 'ipCheck', 'addressCheck', 'isFullIpCheckEnabled'] as $key) {
      if (isset($data[$key]) && !empty($data[$key])) {
        // For addressCheck, use the disallowedPanelsAddressCheck value only; otherwise check the disallowedPanels.
        if ($key == 'addressCheck') {
          $excludePanels = explode(',', $configValidationChecks['disallowedPanelsAddressCheck']);
        }
        else {
          $excludePanels = explode(',', $configValidationChecks['disallowedPanels']);
        }
        if (!empty(array_filter($excludePanels)) && in_array($activePanel, $excludePanels)) {
          $header[$key] = 'FALSE';
          $logMsg .= "; set $activePanel $key from value '" . $data[$key] . "' to FALSE";
        }
        else {
          $header[$key] = $data[$key];
        }
      }
    }
    // \Drupal::logger('lp_lib')->debug($logMsg);
    return $header;
  }

  /**
   * Perform the business of various endpoint calls for POST request.
   */
  protected function postRequest($endpoint, array $data, array $options = []) {
    // Set host and accept response.
    $this->setHostWithAcceptResponse($endpoint);
    // No need to set accept lang for recruitment service endpoint.
    if ($endpoint == 'OneP-RecruitmentServices/Recruitment') {
      $headers = $this->setHeader('recruitmentPost', $data);
    }
    // OP-4526: Log verity challenge questions activity type.
    elseif ($endpoint == 'OneP-AccountServices/Audit/Panelist') {
      $this->setAcceptLanguage();
      $headers = $this->setHeader('VerityChallengeQuestionAuditLog', $data);
      unset($headers['domain']);
    }
    else {
      // Accept Language is set in data for login endpoint.
      // Getting panelist accept lang from get panelist email,
      // endpoint called before login.
      if (isset($data['accept_language'])) {
        $this->acceptLanguage = $data['accept_language'];
        unset($data['accept_language']);
      }
      else {
        $this->setAcceptLanguage();
      }
      // Need to set seperate header for Change Password endpoint - OP-4580.
      if ($endpoint == 'OneP-AccountServices/Panelist/ChangePassword') {
        $headers = $this->setHeader('changePassword', $data);
      }
      // Need to set seperate header for Forget Password endpoint.
      elseif ($endpoint == 'OneP-AccountServices/Panelist/ForgotPassword') {
        $headers = $this->setHeader('ForgotPassword', $data);
      }
      // OP-6137. Not to move agent from Query to header in case of Detect device.
      else {
        $headers = $this->setHeader('post', $data, $endpoint);
      }
    }
    // Header already set so unset quality checks from data.
    $this->unsetQualityChecksFromData($data);
    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    try {
      if (
        $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword' ||
        $endpoint == 'OneP-AccountServices/Panelist/ChangePassword'
      ) {
        $response = \Drupal::httpClient()->post(
          $url,
          [
            'json' => $data,
            'headers' => $headers,
          ]
        );
      }
      else {
        $response = \Drupal::httpClient()->post(
          $url,
          [
            'form_params' => $data,
            'headers' => $headers,
          ]
        );
      }
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      if (
        $endpoint == 'Jade-UsermanagementServices/authenticate' ||
        $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword' ||
        $endpoint == 'OneP-AccountServices/Panelist/ChangePassword'
      ) {
        $wsResponseLog = $this->unsetNuCaptchaForLog($wsResponse);
      }
      else {
        $wsResponseLog = $wsResponse;
      }

      $this->logData('POST', $url, $data, $headers, $wsResponseLog);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business of various endpoint calls for POST request with JSON format.
   *
   * Can be used by any API developed by Atlas Team.
   */
  protected function postJsonRequest($endpoint, array $data, array $options = []) {

    // Set host and accept response.
    $this->setHostWithAcceptResponse($endpoint);
    // Header already set so unset quality checks from data.
    $this->unsetQualityChecksFromData($data);
    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    // Get the options and append as parameter in the url.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }

    try {

      // Modify POST header based on Endpoint Prefix.
      $type = explode("/", $endpoint);
      // Set Accept language, should not call for unsubscribe from email.
      // OP-6055 - To resolve warnings.
      if ((isset($type[0]) && $type[0] != "OneP-Bridge") && (isset($type[1]) && $type[1] != "ACM")) {
        $this->setAcceptLanguage();
      }
      switch ($type[0]) {
        // For all Consent API Post headers.
        case 'KantarConsent':
          $headers = $this->setHeader('UCMpost', $data);
          break;

        // For Forehose  API Post headers.
        case 'ingest':
          $headers = $this->setHeader('Firehosepost', $data);
          break;

        // For Zendesk API Post headers.
        case 'OneP-Bridge':
          // Condition to check for zendesk redeem and survey.
          if ($type[1] == "ZendeskUrl") {
            unset($data['token']);
            $headers = $this->setHeader('gendeskurlpost', $data);
          }
          // Condition to check for unsubscribe from email.
          elseif ($type[1] == "ACM") {
            $this->acceptLanguage = $data['accept-language'];
            unset($data['accept-language']);
            $headers = $this->setHeader('unsubscribeFromEmail', $data);
            unset($headers['accept-language']);
            unset($headers['domain']);
          }
          // Condition to check for Floating Action Button.
          elseif ($type[1] == "Feedback") {
            $headers = $this->setHeader('FloatingActionButton', $data);
          }
          break;

        // For Cookie API Post headers.
        case 'OneP-Cookie':
          $headers = $this->setHeader('cookieUrlPost', $data);
          break;

        // OP-5630: For SSQ API Post headers.
        case 'SaveSSQOffer':
        case 'SaveSSQResponse':
          $headers = $this->setHeader('SSQ', $data);
          break;

        default:
          $headers = $this->setHeader('post', $data);
          break;
      }
      // Notify the logs when Host's X API key is not found.
      if (isset($headers['x-api-key']) && $headers['x-api-key'] == NULL) {
        $logMsg = '<pre>' . t('Sorry! X-API-Key is not found for POST Endpoint') . ' - ' . $url;
        \Drupal::logger('lp_exception')->error($logMsg);
      }

      // Actuall call to POST CURL request.
      $response = \Drupal::httpClient()->post(
        $url,
        [
          'json' => $data,
          'headers' => $headers,
        ]
        );
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('POST', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business login to call PATCH request.
   */
  protected function patchRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    $jsonData = [];
    try {

      $headers = $this->setHeader('patch', $data, $endpoint);
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      // OP-6925 - Added separate header for JoinAPI to pass JSON body and data.
      // OP-7074 - Join API New Endpoint - Pass data to JSON body not in URL Params.
      switch ($endpoint) {
        case 'OneP-AccountServices/EmailVerification/JoinAPI':
          $response = \Drupal::httpClient()->patch($url,
          [
            'json' => $data,
            'headers' => $headers,
          ]);
          break;

        default:
          // Call the api and pass the required parameter.
          // Call the api and pass the required parameter.
          $response = \Drupal::httpClient()->patch($url . '?' . http_build_query($data),
          [
            'headers' => $headers,
          ]);
          break;
      }

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->response = $wsResponse;
      // Set Log response.
      $this->logData('PATCH', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business login to call PATCH request.
   *
   * @param string $endpoint
   *   RIL End point URL name.
   * @param array $data
   *   Can be required data for api end point.
   * @param array $options
   *   Can be URL query params options.
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return object
   *   Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  protected function profileUpdateRequest($endpoint, array $data, array $options = [], $isAjax = FALSE) {
    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    // OP-6946 ajax call to Panelist info update.
    if ($isAjax) {
      $data['ajaxcall'] = TRUE;
    }
    $headers = $this->setHeader('profileupdate', $data);
    // Header already set so unset quality checks from data.
    $this->unsetQualityChecksFromData($data);

    /* Get the params, need to send in url */
    if (isset($options['urlParams'])) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }
    // Get the token.
    if (isset($data['token']) && !empty($data['token'])) {
      $token = $data['token'];
      unset($data['token']);
    }
    else {
      // Throw error.
    }
    // Generate the multipart array.
    $multipart = [
      [
        'name'     => '_method',
        'contents' => 'PATCH',
      ],
      [
        'name'     => 'token',
        'contents' => urlencode($token),
      ],
    ];
    foreach ($data as $key => $value) {
      $multipart[] = [
        'name' => $key,
        'contents' => $value,
      ];
    }

    try {
      $response = \Drupal::httpClient()->patch($url, [
        'multipart' => $multipart,
        'headers'  => $headers,
      ]);
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('PATCH', $url, $multipart, $headers, $wsResponse);
      // OP-6946 - Response data when ajax call to Panelist info update.
      if ($isAjax) {
        $returnResponse = [];
        $returnResponse['errorStatus'] = FALSE;
        $returnResponse['responseCode'] = $wsResponse->httpStatus();
        $returnResponse['responseData'] = $wsResponse->data();
        return $returnResponse;
      }
      else {
        return $wsResponse;
      }
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('PATCH', $url, $multipart, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business login to call DELETE request.
   */
  protected function deleteRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Gernrating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    try {
      $headers = $this->setHeader('delete', $data);
      // Call the api and pass the required parameter.
      $response = \Drupal::httpClient()->delete($url . '?' . http_build_query($data),
      [
        'headers' => $headers,
      ]);
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->response = $wsResponse;
      $this->logData('DELETE', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('DELETE', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business of join api endpoint calls for POST request.
   */
  protected function joinApiPostRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint);
    if (isset($data['locale']) && !empty($data['locale'])) {
      $this->acceptLanguage = $data['locale'];
    }
    else {
      $this->setAcceptLanguage();
      $data['locale'] = $this->acceptLanguage;
    }

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);

    try {
      $headers = $this->setHeader('post', $data);
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      // Call the POST API method and pass the required parameters.
      // unset($headers['accept-language']);.
      $response = \Drupal::httpClient()->post($url,
      [
        'form_params' => $data,
        'headers' => $headers,
      ]);
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('POST', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      if ($data['joinType'] == 'JoinAPI') {
        $this->logData('POST', $url, $data, $headers, $e->getResponse(), json_decode($e->getResponse()->getBody(), TRUE), 'ClientException');
        $this->handleJoinApiException($e, 'ClientException', $data['joinType']);
      }
      else {
        // Process the exception flow.
        $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientException');
      }
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->handleJoinApiException($e, 'RequestException', $data['joinType']);
    }
    catch (ConnectException $e) {
      $this->handleJoinApiException($e, 'ConnectException', $data['joinType']);
      /* To-do: Connect exception handling */
    }
    catch (Exception $e) {

      /* To-do: Normal exception handling */
    }
  }

  /**
   * Perform the business of join api endpoint calls for Get request.
   */
  protected function joinApiGetRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint);
    $this->acceptLanguage = $data['acceptLang'];

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    try {
      $headers = $this->setHeader('get', $data);
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      unset($data['token']);
      unset($data['acceptLang']);

      // OP-7054. Check Portal Join email exist or not.
      if (isset($data['userName']) && isset($data['isEligible']) && $data['isEligible'] == 'TRUE') {
        $data['joinType'] = 'PortalJoin';
      }
      else {
        // Pass dummy username for required field.
        // Prioritized with token. So passed dummy username.
        $data['userName'] = "dummy@dummy.com";
      }
      // Call the api and pass the required parameter.
      $response = \Drupal::httpClient()->get($url . '?' . http_build_query($data),
      [
        'headers' => $headers,
      ]);

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('GET', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      $this->logData('GET', $url, $data, $headers, $e->getResponse(), json_decode($e->getResponse()->getBody(), TRUE), 'ClientException');
      $this->handleJoinAPIException($e, 'ClientException', $data['joinType']);
    }
  }

  /**
   * Perform the business Survey API help center url get request.
   */
  protected function surveyPortalGetRequest($endpoint, array $data, array $options = []) {

    $this->setHostWithAcceptResponse($endpoint);
    if (isset($data['locale']) && !empty($data['locale'])) {
      $this->acceptLanguage = $data['locale'];
    }
    else {
      $this->setAcceptLanguage();
      $data['locale'] = $this->acceptLanguage;
    }

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);

    // Check the options if we need to pass params in URL.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }

    try {
      // Call the api and pass the required parameter.
      $headers = $this->setHeader('surveyUrlGet', $data);
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      $response = \Drupal::httpClient()->get($url . '?' . http_build_query($data),
      [
        'headers' => $headers,
      ]);

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('POST', $url, $data, $headers, $wsResponse);
      return $wsResponse;

    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business Survey API help center url get request.
   */
  protected function surveyPortalPutRequest($endpoint, array $data, array $options = []) {

    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);

    // Check the options if we need to pass params in URL.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }

    try {
      $headers = $this->setHeader('surveyUrlPut', $data);
      // PUT request for API call.
      $response = \Drupal::httpClient()->put($url,
      [
        'json' => $data,
        'headers' => $headers,
      ]);

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('PUT', $url, $data, $headers, $wsResponse);
      return $wsResponse;

    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Perform the business Cookie API for get cookies request.
   */
  protected function cookiePortalGetRequest($endpoint, array $data, array $options = []) {

    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);

    // Check the options if we need to pass params in URL.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }

    try {
      $headers = $this->setHeader('cookieUrlGet', $data);
      // PUT request for API call.
      $response = \Drupal::httpClient()->get($url,
      [
        'json' => $data,
        'headers' => $headers,
      ]);

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response, $endpoint);
      $this->logData('GET', $url, $data, $headers, $wsResponse);
      return $wsResponse;

    }
    catch (ClientException  $e) {
      $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ClientException');
    }
  }

  /**
   * Perform the business of various endpoint calls for AJAX POST request.
   */
  protected function ajaxPostRequest($endpoint, array $data, array $options = []) {

    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);

    try {
      $dataInput = $data;
      $data['ajaxcall'] = TRUE;

      // Need to set seperate header for Change Password endpoint - OP-4580.
      if ($endpoint == 'OneP-AccountServices/Panelist/ChangePassword') {
        $headers = $this->setHeader('changePassword', $data);
      }
      // Need to set seperate header for Forget Password endpoint.
      elseif ($endpoint == 'OneP-AccountServices/Panelist/ForgotPassword') {
        $headers = $this->setHeader('ForgotPassword', $data);
      }
      // Need to set seperate header for Recruitment endpoint - OP-6864.
      elseif ($endpoint == 'OneP-RecruitmentServices/Recruitment') {
        $headers = $this->setHeader('recruitmentPost', $data);
      }
      else {
        $headers = $this->setHeader('post', $dataInput);
      }
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);

      if (
        $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword' ||
        $endpoint == 'OneP-AccountServices/Panelist/ChangePassword'
      ) {
        $response = \Drupal::httpClient()->post($url,
        [
          'json' => $dataInput,
          'headers' => $headers,
        ]);
      }
      else {
        $response = \Drupal::httpClient()->post($url,
        [
          'form_params' => $dataInput,
          'headers' => $headers,
        ]);
      }

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);

      // Unset nucaptcha html from log.
      if (
        $endpoint == 'OneP-AccountServices/Panelist' ||
        $endpoint == 'OneP-RecruitmentServices/PanelistPreRegistration'
      ) {
        $wsResponseLog = $this->unsetNuCaptchaForLog($wsResponse);
      }
      else {
        $wsResponseLog = $wsResponse;
      }
      $this->logData('POST', $url, $data, $headers, $wsResponseLog);
      $returnResponse = [];
      $returnResponse['errorStatus'] = FALSE;
      $returnResponse['responseCode'] = $wsResponse->httpStatus();
      $returnResponse['responseData'] = $wsResponse->data();
      return $returnResponse;

    }
    catch (ClientException  $e) {
      // Process the exception flow.
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      return $this->processExceptionFlow('POST', $url, $data, $headers, $endpoint, $e, 'Exception');
    }

  }

  /**
   * Perform the business login to call AJAx PATCH request.
   */
  protected function ajaxPatchRequest($endpoint, array $data, array $options = []) {
    $this->setHostWithAcceptResponse($endpoint);
    $this->setAcceptLanguage();

    // Generating the API URL. host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    try {
      $data['ajaxcall'] = TRUE;
      $dataInput = $data;

      $headers = $this->setHeader('patch', $dataInput, $endpoint);
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      // OP-6925 - Added separate header for JoinAPI to pass JSON body and data.
      // OP-7074 - Join API New Endpoint - Pass data to JSON body not in URL Params.
      switch ($endpoint) {
        case 'OneP-AccountServices/EmailVerification/JoinAPI':
          $response = \Drupal::httpClient()->patch(
            $url,
            [
              'json' => $data,
              'headers' => $headers,
            ]
          );
          break;

        default:
          // Call the api and pass the required parameter.
          $response = \Drupal::httpClient()->patch(
            $url . '?' . http_build_query($data),
            [
              'headers' => $headers,
            ]
          );
          break;
      }
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);

      // Unset nucaptcha html from log.
      if ($endpoint == 'OneP-AccountServices/EmailVerification/JoinAPI') {
        $wsResponseLog = $this->unsetNuCaptchaForLog($wsResponse);
      }
      else {
        $wsResponseLog = $wsResponse;
      }

      $this->logData('PATCH', $url, $data, $headers, $wsResponseLog);
      $returnResponse = [];
      $returnResponse['errorStatus'] = FALSE;
      $returnResponse['responseCode'] = $wsResponse->httpStatus();
      $returnResponse['responseData'] = $wsResponse->data();
      return $returnResponse;

    }
    catch (ClientException  $e) {
      // Process the exception flow.
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      return $this->processExceptionFlow('PATCH', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Imperium Verity API to validate US address verification.
   *
   * @param [array] $panelistData
   *   List of Panelist Data.
   *
   * @return [array]   array with verity score and other useful information
   */
  protected function verityAddressValidatePostRequest($data, $type) {

    $config = \Drupal::service('settings');
    // Getting the Verity API configs.
    $endpointURL = $config::get('lp')['verity']['endpointURL'];
    $ContentType = $config::get('lp')['verity']['Content-Type'];

    // Set Verity Auth Header array.
    $authHeaders = [
      'Username' => $config::get('lp')['verity']['username'],
      'Password' => $config::get('lp')['verity']['password'],
    ];

    $requestData = [];
    if ($type == 'verityScore') {
      // Extracting Panelist data required for Verity API endpoint.
      $requestData = [
        'ClientID' => $config::get('lp')['verity']['ClientID'],
        'FirstName' => $data['firstName'],
        'LastName' => $data['lastName'],
        'AddLine1' => $data['address1'],
        'AddLine2' => isset($data['address2']) ? $data['address2'] : '',
        'PostalCode' => $data['postalCode'],
        'CountryCode' => $data['country'],
        // Convert seconds into a specific format "12/30/2018 05:37 PM".
        'DOB' => date("Ym", strtotime($data['dateOfBirth'])),
        'SSN4' => '',
        'OutputCase' => $config::get('lp')['verity']['OutputCase'],
        'QueryVerityPlus' => $config::get('lp')['verity']['QueryVerityPlus'],
        'QueryChallenge' => $config::get('lp')['verity']['QueryChallenge'],
        'IPAddress' => MappingUsages::get_client_ip_address(),
      ];
      $xmlType = 'ValidateDataPlus9';
    }
    elseif ($type == 'ChallengeScore') {
      // Extracting data required for Verity API endpoint.
      $requestData = [
        'ClientID' => $config::get('lp')['verity']['ClientID'],
        'ChallengeID' => $data['ChallengeID'],
        'AnswerChoice1' => $data['AnswerChoice1'],
        'AnswerChoice2' => $data['AnswerChoice2'],
        'AnswerChoice3' => $data['AnswerChoice3'],
      ];
      $xmlType = 'ChallengeValidate';
    }
    // Creating XML raw response data
    // *****************8**** XML DATA : start Here ****************************.
    $xmlRequestData = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Header>';
    // Converting Verity Authorization Header array into its relevant XML form.
    $xmlRequestData .= '<AuthHeader xmlns="http://tempuri.org/">';
    foreach ($authHeaders as $key => $value) {
      $xmlRequestData .= '<' . $key . '>' . $value . '</' . $key . '>';
    }
    $xmlRequestData .= '</AuthHeader>';
    $xmlRequestData .= '</soap:Header><soap:Body><' . $xmlType . ' xmlns="http://tempuri.org/">';
    // Converting Verity Request array into its relevant XML form.
    foreach ($requestData as $key => $value) {
      $xmlRequestData .= '<' . $key . '>' . $value . '</' . $key . '>';
    }
    $xmlRequestData .= '</' . $xmlType . '></soap:Body></soap:Envelope>';
    // ************************* XML DATA : Ends Here ****************************
    try {
      // Call the api and pass the required parameter to get access token.
      $response = \Drupal::httpClient()->post($endpointURL, [
        'body' => $xmlRequestData ,
        'headers' => [
          'Content-Type' => $ContentType,
          'content-length' => strlen($xmlRequestData),
        ],
      ]);
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response, 'verityws');
      // Set Logging Data for Verity response.
      $responseData = $response->getBody();

      // Remove <soap> or <s> tag from string.
      $responseData1 = str_replace("<s:Body>", "", $responseData);
      $responseData2 = str_replace("<soap:Body>", "", $responseData1);
      $responseData3 = str_replace("</s:Body>", "", $responseData2);
      $responseXML = str_replace("</soap:Body>", "", $responseData3);
      $responseXMLObject = simplexml_load_string($responseXML);

      if ($type == 'verityScore') {
        // Extract required Verity info.
        $responseData = [
          'verityScore' => $responseXMLObject->ValidateDataPlus9Response->ValidateDataPlus9Result->Score->asXML(),
          'messageCodes' => $responseXMLObject->ValidateDataPlus9Response->ValidateDataPlus9Result->MessageCodes->asXML(),
          'ErrorMsg' => $responseXMLObject->ValidateDataPlus9Response->ValidateDataPlus9Result->ErrorMsg->asXML(),
        ];
      }
      elseif ($type == 'ChallengeScore') {
        // Extract required challenge response info.
        $responseData = [
          'ChallengeID' => strip_tags($responseXMLObject->ChallengeValidateResponse->ChallengeValidateResult->ChallengeID->asXML()),
          'ChallengeScore' => strip_tags($responseXMLObject->ChallengeValidateResponse->ChallengeValidateResult->ChallengeScore->asXML()),
          'ErrorMsg' => strip_tags($responseXMLObject->ChallengeValidateResponse->ChallengeValidateResult->ErrorMsg->asXML()),
        ];
      }
      $this->logData('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $responseData);
      // Logs data ends.
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('POST', $endpointURL, $requestData, $authHeaders, $responseXMLObject, $e, 'Exception');
    }
  }

  /**
   * Perform the curiosity PUT core endpoint call.
   */
  protected function putJsonRequest($endpoint, array $data, array $options = []) {
    $this->setAcceptLanguage();
    $this->setHostWithAcceptResponse($endpoint, 'PUT');
    // Gernrating the API URL host/endpoint.
    $url = $this->setEndpointUrl($endpoint);
    // Check the options if we need to pass params in URL.
    if (count($options)) {
      $urlParams = implode("/", $options);
      $url .= "/" . $urlParams;
    }
    try {
      // Modify PUT header based on Endpoint Prefix.
      $type = explode("/", $endpoint);
      switch ($type[0]) {
        case 'answer':
          $headers = $this->setHeader('curiosityCorePut', $data);
          break;

        default:
          $headers = $this->setHeader('PUT', $data);
          break;
      }
      // Header already set so unset quality checks from data.
      $this->unsetQualityChecksFromData($data);
      // Call the api and pass the required parameter.
      $response = \Drupal::httpClient()->put(
        $url . '?' . http_build_query($data),
        [
          'json' => $data,
          'headers' => $headers,
        ]
      );
      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('PUT', $url, $data, $headers, $wsResponse);
      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ClientException');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'BadResponseException');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'RequestException');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'ConnectException');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      return $this->processExceptionFlow('PUT', $url, $data, $headers, $endpoint, $e, 'Exception');
    }
  }

  /**
   * Send the adobe analytics to adobe server.
   */
  protected function adobeAnalyticsGetRequest($endpoint, array $data, array $options = []) {

    $url = $endpoint;
    try {
      $headers = $this->setHeader('surveyFinishGet', $data);
      if (isset($headers['domain'])) {
        unset($headers['domain']);
      }

      $response = \Drupal::httpClient()->get($url, [
        'headers' => $headers,
      ]);

      // Create a object of common service response.
      $wsResponse = new CommonServiceResponse();
      // Set the response.
      $wsResponse->setResponse($response);
      $this->logData('GET', $url, $data = [], $headers, $wsResponse, '', '', 'lp_surveyfinish');

      return $wsResponse;
    }
    catch (ClientException  $e) {
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data = [], $headers, $endpoint = [], $e, 'ClientException', 'lp_surveyfinish');
      // Throw new \Exception($wsResponse->httpMsg());
    }
    catch (ClientErrorResponseException $e) {
      /* To-do: Client error response exception handling */
      // Process the exception flow.
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ClientErrorResponseException', 'lp_surveyfinish');
    }
    catch (ServerErrorResponseException $e) {
      /* To-do: Server error response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ServerErrorResponseException', 'lp_surveyfinish');
    }
    catch (BadResponseException $e) {
      /* To-do: Bad response exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'BadResponseException', 'lp_surveyfinish');
    }
    catch (RequestException $e) {
      /* To-do: Any Request exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'RequestException', 'lp_surveyfinish');
    }
    catch (ConnectException $e) {
      /* To-do: Connect exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'ConnectException', 'lp_surveyfinish');
    }
    catch (Exception $e) {
      /* To-do: Normal exception handling */
      $this->processExceptionFlow('GET', $url, $data, $headers, $endpoint, $e, 'Exception', 'lp_surveyfinish');
    }
  }

}
