<?php

namespace Drupal\lp_lib\WsClient;

use Psr\Http\Message\ResponseInterface;
use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;

/**
 * WS response object for common service.
 */
class CommonServiceResponse implements WsResponseInterface
{

  protected $httpStatus;
  protected $httpMsg;
  protected $data;
  protected $errorCode;
  protected $errorMsg;
  protected $warningMsg;
  protected $encoder;

  /**
   * Empty constroctor, no need to set default variables.
   */
  public function __construct()
  {
  }

  /**
   * Function for setting the response data, received form api callback.
   */
  public function setResponse(ResponseInterface $response, $endpoint = NULL)
  {
    $this->httpStatus = $response->getStatusCode();
    $this->httpMsg    = $response->getReasonPhrase();
    $this->errorCode  = NULL;
    $this->errorMsg   = NULL;
    $this->warningMsg = NULL;
    $this->data       = NULL;
    $this->metaData   = NULL;

    if ($endpoint == 'curiosityCoreGet') {
      $data = $response->getBody()->getContents();
      $this->data = $data;
    }
    // To get Verity API XML response data.
    elseif ($endpoint == 'verityws') {
      $data = $response->getBody();
      $this->data = $data;
    } else {
      $data = json_decode($response->getBody(), TRUE);
      if (isset($data['data']) && !empty($data['data'])) {
        $this->data = $data['data'];
        // Extracting response contain meta in respponse.
        if (isset($data['meta']) && !empty($data['meta'])) {
          $this->metaData = $data['meta'];
        }
      } else {
        $this->data = $data;
      }
    }
    $this->validateStatus($endpoint);
  }

  /**
   * Getter for http status.
   */
  public function httpStatus()
  {
    return $this->httpStatus;
  }

  /**
   * Getter for http Message.
   */
  public function httpMsg()
  {
    return $this->httpMsg;
  }

  /**
   * Function to check error.
   */
  public function isError()
  {
    return isset($this->errorCode);
  }

  /**
   * Getter for error code.
   */
  public function errorCode()
  {
    return $this->errorCode;
  }

  /**
   * Getter for error Message.
   */
  public function errorMsg()
  {
    return $this->errorMsg;
  }

  /**
   * Getter for warning Message.
   */
  public function warningMsg()
  {
    return $this->warningMsg;
  }

  /**
   * Getter for response data.
   */
  public function data()
  {
    if (isset($this->data)) {
      return $this->data;
    }
    return NULL;
  }

  /**
   * Getter for response data.
   */
  public function metaData()
  {
    if (isset($this->metaData)) {
      return $this->metaData;
    }
    return NULL;
  }

  /**
   * Validate the Status if there is any error.
   * Mapping the error codes/type.
   */
  public function validateStatus($endpoint = NULL)
  {

    if (substr($this->httpStatus(), 0, 1) != "2") {
      // $exception_msg = "HTTP error. Status: " . $this->httpStatus() . ", Message: " . $this->errorMsg();
      // Perform switch case on error type. can also have with httpStatus (200,503 etc).
      // parsing error message.
      $errorMessage = isset($this->data['message']) ? $this->data['message'] : NULL;
      $errorMessage = json_decode($errorMessage, TRUE);
      if (isset($errorMessage) && is_array($errorMessage) && !empty($errorMessage)) {
        $errorMessageStr = '';
        foreach ($errorMessage as $eMkey => $eMvalue) {
          $errorMessageStr .= $eMkey . ' ';
          foreach ($eMvalue as $ermsg) {
            $errorMessageStr .= $ermsg . ' ';
          }
        }
        if ($errorMessageStr != '') {
          $this->data['message'] = $errorMessageStr;
        }
      }

      // Check if type empty and error set.
      if ((!isset($this->data['type']) || empty($this->data['type'])) && isset($this->data['error'])) {
        $this->data['type'] = $this->data['error'];
      }

      // Update mapping for full auth require.
      if ($this->httpStatus() == '401') {
        if (strpos($endpoint, 'UnsubscribeFromEmail') !== FALSE) {
          $this->data['type'] = 'default';
        } else {
          $this->data['type'] = 'invalid_token';
        }
      }

      // Validate the response for nucaptcha.
      // Error code should be 428 or 412.
      // Captcha validation response should be one of 'CaptchaPending', 'CaptchaWrong', 'CaptchaEmpty'.
      // Error type should be one of 'ERR_NUDETECT_CAPTCHA_REQUIRED', 'ERR_NUDETECT_CAPTCHA_FAILED'.
      if (
        ($this->httpStatus() == '428' || $this->httpStatus() == '412') &&
        isset($this->data['nuDetect']['captchaValidateResponse']) &&
        isset($this->data['nuDetect']['captchaHtml']) &&
        isset($this->data['type']) &&
        in_array($this->data['nuDetect']['captchaValidateResponse'], ['CaptchaPending', 'CaptchaWrong', 'CaptchaEmpty']) &&
        in_array($this->data['type'], ['ERR_NUDETECT_CAPTCHA_REQUIRED', 'ERR_NUDETECT_CAPTCHA_FAILED']) &&
        ($endpoint == 'Jade-UsermanagementServices/authenticate' ||
          $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword' ||
          $endpoint == 'OneP-AccountServices/Panelist/ChangePassword'
        )
      ) {
        // Retrive the captcha html from response.
        // Captchavalidateresponse would be CaptchaPending, CaptchaWrong, CaptchaEmpty.
        // CaptchaCorrect validation response would be handle in 200 status.
        $nuCaptchaResponse = [
          'captchaHtml' => $this->data['nuDetect']['captchaHtml'],
          'captchaValidateResponse' => $this->data['nuDetect']['captchaValidateResponse'],
          'errorType' => $this->data['type'],
        ];

        // Set captcha response in session and retrive on login page to display.
        $request = \Drupal::service('request_stack')->getCurrentRequest();
        $nucaptchaSession = $request->getSession();
        $nucaptchaSession->set('nucaptchaSession', $nuCaptchaResponse);

        // Redirect to page for showing the nucaptcha.
        $redirectResponse = \Drupal::service('redirect_response');
        $redirectResponse->setTargetUrl($request->getRequestUri());
        $redirectResponse->send();
        return;
      }

      if (isset($this->data['type'])) {
        switch ($this->data['type']) {
          case 'ERR_CINT_GENERAL':
            if ($this->httpStatus() == '422') {
              $cintMessgae = $this->data['message'];
              // Check the message is for duplicate panelist.
              if (
                strpos($cintMessgae, 'email_address') !== FALSE &&
                strpos($cintMessgae, 'has already been taken') !== FALSE
              ) {
                $this->errorMsg = CommonMessenger::errorMessageMapping('user_exist');
              }
            }
            break;

            // If error general then check the status with endpoint.
          case 'ERR_GENERAL':
            if ($this->httpStatus() == '403' && ($endpoint == 'Jade-UsermanagementServices/authenticate' || $endpoint == 'OneP-AccountServices/TokenLogin')) {
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_active");
            } elseif (substr($this->httpStatus(), 0, 1) == '4' && ($endpoint == 'Jade-UsermanagementServices/authenticate' || $endpoint == 'OneP-AccountServices/PanelistInfo' || $endpoint == 'OneP-AccountServices/ResendDoi')) {
              // Modify I found nothing error.
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found");
            } elseif ($this->httpStatus() == '404' && $endpoint == 'OneP-AccountServices/Panelist/ForgotPassword') {
              // Op-5171 : User not found error message change for forget password page.
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found_forgotpassword");
            } elseif ($this->httpStatus() == '404' && $endpoint == 'OneP-AccountServices/Panelist') {
              // Get Panelist Via Email endpoint.
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found");
            } elseif ($this->httpStatus() == '409' && $endpoint == 'OneP-AccountServices/Panelist') {
              // Modify Already exist error for registration.
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_exist");
            } elseif ($this->httpStatus() == '400' && $endpoint == 'OneP-AccountServices/Panelist') {
              // Modify patch update request error for whats new block.
              $this->errorMsg = CommonMessenger::errorMessageMapping("something_went_wrong");
            } elseif ($endpoint == 'OneP-Cookie/ConsentCookie/PanelistId') {
              $this->errorMsg = CommonMessenger::errorMessageMapping("cookies_not_available_for_panelist");
            } elseif ($this->httpStatus() == '404' && $endpoint == 'OneP-Bridge/ACM/UnsubscribeFromEmail') {
              // Modify Already exist error for registration.
              $this->errorMsg = CommonMessenger::errorMessageMapping("account_closed");
            } elseif ($this->httpStatus() == '400' && $endpoint == 'OneP-AccountServices/Panelist/ChangeAccount/Verification') {
              // OP-4166
              // Passing Current password wrong error message status for change email.
              $this->errorMsg = 'ERR_NEW_PASSWORD_INCORRECT';
            } else {
              // If message is coming from RIL but not mapped.
              $this->errorMsg = CommonMessenger::errorMessageMapping("err_general_message_common");
              // $this->errorMsg = MappingUsages::getHumanReadableMessage( $this->data['message']);
            }
            break;

          case 'ERR_USER_NOT_FOUND':
            $cintMessgae = $this->data['message'];
            if ($this->httpStatus() == '404' && $endpoint == 'Jade-UsermanagementServices/authenticate' && strpos($cintMessgae, 'unsubscribed') !== FALSE) {
              $this->errorMsg = CommonMessenger::errorMessageMapping("account_closed");
            } elseif ($endpoint == 'OneP-AccountServices/Panelist/ForgotPassword') {
              // Op-5171 : User not found error message change for forget password page.
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found_forgotpassword");
            } else {
              $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found");
            }

            break;

          case 'ERR_ACCESS_DENIED_INVALID_CREDENTIALS':
            $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_found");
            break;

          case 'ERR_ACCESS_DENIED_ROLE_INIT':
            $this->errorMsg = CommonMessenger::errorMessageMapping("user_access_denied_role");
            break;

          case 'ERR_FAILED_COUNTRY_IP_CHECK':
          case 'ERR_FAILED_FULL_IP_CHECK':
            $this->warningMsg = CommonMessenger::warningMessageMapping("ip_check_fail");
            break;

          case 'FAILED_FULL_IP_CHECK_LOGIN':
          case 'FAILED_FULL_IP_CHECK_ACTIVATION':
            $this->warningMsg = CommonMessenger::warningMessageMapping("ip_address_check_fail");
            break;

          case 'ERR_INVALID_PARAMETERS':
            $this->errorMsg = MappingUsages::getHumanReadableMessage($this->data['message']);
            break;

          case 'ERR_EMAIL_ALREADY_EXISTS':
            $this->errorMsg = CommonMessenger::errorMessageMapping("email_already_exists");
            break;

          case 'ERR_DUPLICATE_HO_TRANSACTION_ID':
            $this->errorMsg = CommonMessenger::errorMessageMapping("err_duplicate_ho_transaction_id");
            break;

          case 'ERR_FAILED_ADDRESS_CHECK':
            $this->warningMsg = CommonMessenger::warningMessageMapping("ip_address_check_fail");
            break;

          case 'ERR_INVALID_EMAIL':
            if (trim($this->data['message']) == "FAILED_IMPERIUM_REALMAIL_CHECK | Default Message: Can't accept that!") {
              $this->errorMsg = CommonMessenger::errorMessageMapping("failed_imperium_check");
            } else {
              $this->errorMsg = CommonMessenger::errorMessageMapping("email_invalid");
            }

            break;

          case 'invalid_token':
            // Check if user is on password reset page.
            // Get Current Page URL.
            $request = \Drupal::service('request_stack')->getCurrentRequest();
            $requestUrl = $request->server->get('REQUEST_URI', NULL);
            if (preg_match('/(password-reset)/', $requestUrl)) {
              $this->errorMsg = CommonMessenger::errorMessageMapping("reset_password_link_expire");
            } else {
              $this->errorMsg = CommonMessenger::errorMessageMapping("token_expired");
            }
            break;

          case 'ERR_OLD_PASSWORD_INCORRECT':
            $this->errorMsg = $this->data['message'];
            break;

          case 'ERR_DOI_ALREADY_DONE':
            // OP- 4943: Allow panelist to retry challenge question only once.
            // If DOI already done then redirect panelist to login page.
            $this->errorMsg = CommonMessenger::errorMessageMapping("ERR_DOI_ALREADY_DONE");
            if (strpos($endpoint, 'DoiToken') !== FALSE) {
              $redirectResponse = \Drupal::service('redirect_response');
              // Call to Recreate URL when Default EN lang come in URL or as default Panel.
              $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_login.Login', ['re' => 'doidone']));
              $redirectResponse->send();
            }
            break;

            // OP-5947 IP Address Capping.
            // Panelist Ativate end point have respose error type ERR_FAILED_REPEAT_IP_CHECK.
            // Click on doi link in mail redirect to home page with error message.
          case 'ERR_FAILED_REPEAT_IP_CHECK':
            $this->errorMsg = CommonMessenger::errorMessageMapping("err_duplicate_ho_transaction_id");
            $redirectResponse = \Drupal::service('redirect_response');
            // Call to Recreate URL when Default EN lang come in URL or as default Panel.
            $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('<front>', ['re' => 'repeatip']));
            $redirectResponse->send();
            break;

          case 'ERR_FAILED_CHALLENGE_QUESTION':
            // OP- 7095: Handle Error Types for Login.
            // Panelist Login end point have respose error type as ERR_FAILED_CHALLENGE_QUESTION.
            $this->errorMsg = CommonMessenger::errorMessageMapping("failed_challenge_question");
            $redirectResponse = \Drupal::service('redirect_response');
            $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('<front>'));
            $redirectResponse->send();
            break;

          case 'ERR_ACCESS_DENIED_OTHER':
            // OP- 7095: Handle Error Types for Login.
            // Panelist Login end point have respose error type as ERR_ACCESS_DENIED_OTHER.
            $this->errorMsg = CommonMessenger::errorMessageMapping("user_not_active");
            $redirectResponse = \Drupal::service('redirect_response');
            $redirectResponse->setTargetUrl(MappingUsages::recreateUrlWithLocaleForDefaultPanel('<front>'));
            $redirectResponse->send();
            break;

          default:
            // If message is coming from RIL but not mapped.
            $this->errorMsg = CommonMessenger::errorMessageMapping("err_general_message_common");
            break;
        }
      }
    }
  }
}
