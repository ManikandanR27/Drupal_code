<?php

namespace Drupal\lp_lib\Util;

use Drupal\Core\Url;

/**
 * This class will have all common functions related to mapping.
 */
class CommonMessenger {

  /**
   *
   */
  public function __construct() {
  }

  /**
   * Get custom error or warning messgae header w.r.t field type.
   *
   * @param [string] $fieldType
   *   Field Type name.
   *
   * @return [string] header name
   */
  public static function errorMessageHeaderMapping($fieldType) {
    switch ($fieldType) {
      case 'user_not_found':
      case 'user_not_found_forgotpassword':
      case 'reset_password_link_expire':
        return t("Please Try Again");

      case 'user_not_active':
      case 'verity_check_fail':
        return t("Temporarily Unavailable");

      case 'user_access_denied_role':
      case 'doi_by_email_link_param_missing':
      case 'ERR_INVALID_URL':
      case 'resend_activated_mail_error':
        return t("Membership Not Verified");

      case 'email_notfound':
        return t("Account Not Found");

      case 'account_closed':
        return t("Access Denied");

      case 'session_timeout':
      case 'panelist_session_timeout':
      case 'token_expired':
        return t("Session Expired");

      case 'error_submit_form':
      case 'join_api_missing_param_error':      
        return t("Technical Difficulties");
      // OP-6769.
      case 'email_empty_joinapi':
      case 'join_api_missing_param_error_new': 
      case 'join_api_duplicate_error_new':     
        return t("Recruitment Attempt Incomplete");
      case 'ERR_DOI_ALREADY_DONE':
      case 'email_already_exists':
      case 'join_api_duplicate_error':
      case 'user_exist':
        return t("Account Already Created");

      case 'ip_check_fail':
      case 'ip_address_check_fail':
        return t("Sorry To Inform You");

      case 'country_not_qualified':
        return t("So Close!");
      case 'recruiter_eligible':
        return t("Eligible For Recruitment");
      case 'recruiter_not_eligible':
        return t("Not Eligible For Recruitment");

      default:
        return t("Error message");
    }
  }

  /**
   * Get custom error messgae w.r.t field type.
   *
   * @param [string] $fieldType
   *   Field Type name.
   *
   * @return [string] Error message code/prefix
   */
  public static function errorMessageMapping($fieldType, $options = []) {
    switch ($fieldType) {

      // Terms.
      case 'term_required':
        return t("You've forgot to agree with the Terms and Conditions. Please take a moment to acknowledge them. Don't forget to tick the box.");

      // First name.
      case 'first_name_required':
        return t("First name is required.");

      case 'first_name_regex':
        return t("Sorry. First name can have only alphabetic characters and the following special characters . - _  Let's try again.");

      case 'first_name_short':
        return t("First name must be 1-30 characters in length.");

      case 'first_name_long':
        return t("First name must be 1-30 characters in length.");

      // Last Name.
      case 'last_name_required':
        return t("Last name is required.");

      case 'last_name_regex':
        return t("Sorry. Last name can have only alphabetic characters and the following special characters . - _");

      case 'last_name_short':
        return t("Last name must be 1-30 characters in length.");

      case 'last_name_long':
        return t("Last name must be 1-30 characters in length.");

      // Email.
      case 'email_required':
        return t('Email address is required.');

      case 'confirm_email_required':
        return t('Confirm your email address is valid.');

      case 'email_invalid':
        return t('Enter a valid email address like john_doe@email.com.');

      case 'email_long':
        return t('Email address must be less than 50 characters.');

      case 'email_notfound':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd We're sorry. We couldn't find your email in our records. Please try again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4><br>")]);

      case 'email_match':
        return t('Emails must match. Try again.');

      // Password.
      case 'password_exist_required':
        return t('Password is required.');

      case 'password_new_required':
        return t('Password is required.');

      case 'password_current_required':
        return t('Enter your old password.');

      case 'current_password_required':
        return t('Enter your Current Password');

      case 'password_confirm_required':
        return t('Confirm your password.');

      case 'password_regex':
        return t("Password must contain a digit, at least one lower case, one upper case, and one of these: !@#$%^&*. Finally must be from 6-20 chars.");

      case 'password_short':
        return t('Password must be 6-20 characters in length.');

      case 'password_long':
        return t('Password must be 6-20 characters in length.');

      case 'password_confirm':
        return t('Passwords must match. Try again.');

      // Postal Code.
      case 'postal_code_required':
        return t('Zip code is required.');

      case 'postal_code_us':
        return t('Enter a valid 5-digit Zip Code.');

      // OP-7085 Postalcode validation for Lebanon panel
      case 'postal_code_lb':
        return t('Enter a valid 4-digit Zip Code.');

      case 'postal_code_au':
      case 'postal_code_za':
      case 'postal_code_ch':
      case 'postal_code_nz':
        return t('Please enter a valid 4-digit postcode.');

      case 'postal_code_gb':
        return t('Please enter a valid postcode (ex. AB12 3CE) with the space.');

      case 'postal_code_uk':
        return t('Please enter a valid postcode (ex. AB12 3CE) with the space.');

      case 'postal_code_in':
        return t('Please enter a valid 6-digit Postal Index Number.');

      case 'postal_code_ar':
        return t('Please enter a valid Código Postal (ex. A1234ABC or A1234).');

      case 'postal_code_at':
      case 'postal_code_be':
      case 'postal_code_dk':
      case 'postal_code_hu':
      case 'postal_code_ve':
        return t('Please enter a valid 4-digit Postal Code.');

      case 'postal_code_br':
        return t('Please enter a valid Código de Endereçamento Postal (for example, 12345-123) with the hyphen.');

      case 'postal_code_ca':
        return t('Please enter a valid Postal Code (for example, A1B 2C3) with the space.');

      case 'postal_code_cn':
      case 'postal_code_ro':
      case 'postal_code_ru':
      case 'postal_code_sg':
      case 'postal_code_vn':
        return t('Please enter a valid 6-digit Postal Code.');

      case 'postal_code_co':
        return t('Please enter a valid 6-digit Código Postal.');

      case 'postal_code_cz':
        return t('Please enter a valid 5-digit Poštovní směrovací číslo (with the space).');

      case 'postal_code_fi':
      case 'postal_code_mx':
      case 'postal_code_kr':
      case 'postal_code_es':
      case 'postal_code_th':
        return t('Please enter a valid 5-digit Postal Code.');

      case 'postal_code_fr':
        return t('Please enter a valid 5-digit Code Postal.');

      case 'postal_code_de':
        return t('Please enter a valid 5-digit Postleitzahl.');

      case 'postal_code_gr':
        return t('Please enter a valid 5-digit Postal Code (with the space).');

      case 'postal_code_id':
        return t('Please enter a valid 5-digit Kodepos.');

      case 'postal_code_ie':
        return t('Please enter a valid Eircode (with the space, e.g. D03 P6K7)');

      case 'postal_code_it':
        return t('Please enter a valid 5-digit Codice di Avviamento Postale.');

      case 'postal_code_jp':
        return t('Please enter a valid 7-digit Postal Code (with the hyphen).');

      case 'postal_code_my':
        return t('Please enter a valid 5-digit Poskod.');

      case 'postal_code_nl':
        return t('Please enter a valid Postcode (for example, 1234 AB) with the space.');

      case 'postal_code_no':
        return t('Please enter a valid 4-digit Postnummer.');

      case 'postal_code_ph':
        return t('Please enter a valid 4-digit ZIP Code.');

      case 'postal_code_pl':
        return t('Please enter a valid Pocztowy Numer Adresowy (for example, 12-345).');

      case 'postal_code_pt':
        return t('Please enter a valid Código Postal (for example, 1234-567) with the hyphen.');

      case 'postal_code_se':
        return t('Please enter a valid 5-digit Postnummer (no space).');

      case 'postal_code_tw':
        return t('Please enter a valid 3 or 5-digit Youdi Quhao.');

      case 'postal_code_tr':
        return t('Please enter a valid 5-digit Posta Kodu.');

      case 'postal_code_cl':
        return t('Please enter the first 3 digits or full 7-digit valid Código Postal.');

      case 'postal_code_pe':
        return t('Please enter a valid 5-digit Código Postal.');

      // DOB.
      case 'dob_required':
        return t('Date of birth is required.');

      case 'dob_invalid':
        return t('Valid date of birth must be MM/DD/YYYY.');

      case 'dob_young':
        if ($options['age']['min']) {
          return t("Sorry. You are not old enough to become a member of LifePoints but we would love to hear from you when you're " . $options['age']['min'] . " or older.");
        }
        else {
          return t("Sorry. You are not old enough to become a member of LifePoints but we would love to hear from you when you're 18 or older.");
        }
      case 'dob_old':
        return t('Please select a valid date of birth.');

      // Gender.
      case 'gender':
        return t('Please select your Gender.');

      // Mailing Address 1.
      case 'mailing_address1_required':
        return t('Address of primary residence is required.');

      case 'mailing_address1_regex':
        return t("Your mailing address can only be up to 80 alphanumeric characters including . - , _ # / ° º ª ' ` ( ). Let's try again.");

      // Mailing Address 2.
      case 'mailing_address2_required':
        return t('Address of primary residence is required.');

      case 'mailing_address2_regex':
        return t("Your mailing address can only be up to 80 alphanumeric characters including . - , _ # / ° º ª ' ` ( ). Let's try again.");

      // State.
      case 'state_regex':
        return t("Your state name can only be up to 50 alphanumeric characters including ' ` . - ( ) ª. Let's try again.");

      case 'state_required_select':
        return t('State you live in is required.');

      case 'state_required_textfield':
        return t('State you live in is required.');

      // City.
      case 'city_required_select':
        return t('City you live in is required.');

      case 'city_required_textfield':
        return t('City you live in is required.');

      case 'city_regex':
        return t("Your city name can only be up to 50 alphanumeric characters including ' ` . - ( ) ª. Let's try again.");

      case 'city_other_required':
        return t('City field is required, Please enter your city.');

      case 'city_other_regex':
        return t("Your city name can only be up to 50 alphanumeric characters including ' ` . - ( ) ª");

      // "Error while submitting form"
      case 'error_submit_form':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, looks like something went wrong on our end while submitting the form. Please try again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // "Token expired"
      case 'token_expired':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, your login session is expired and we couldn't submit your request. Please try to log in again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // OP-3303.
      // "User not found".
      case 'user_not_found':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Email or password was entered incorrectly. If you have forgotten your password or email address, click @forgotpasslinkstart here @forgotpasslinkend or contact our Help Center for more information.", array('@forgotpasslinkstart' => t("<a href='" . MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_login.forgetPassword') . "' class='forgot-your-password'>"), '@forgotpasslinkend' => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")));

      // OP-3303.
      // "User not clicked on confirmation link".
      case 'user_access_denied_role':
      case 'doi_by_email_link_param_missing':
      case 'ERR_INVALID_URL':
      case 'resend_activated_mail_error':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd When you first registered, we sent an email containing a link to verify this email address. Click @resenddoilinkstart here @resenddoilinkend and we will send you a new link to verify your membership.", ['@resenddoilinkstart' => t("<a class='resenddoiemail-box' href='#' data-toggle='modal' data-target='#resenddoiemail-box'>"), '@resenddoilinkend' => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // "IP checks fails".
      // Update the message for ip/address check faile as user_not_active - OP-4411.
      /*
      case 'ip_check_fail':
      return t("We are sorry to inform you that you have not qualified for our community. There are many reasons why people don't qualify, which include, but may not be limited to, not fitting the screening criteria, not providing quality responses or other reasons defined by our partners.We highly recommend you try again in 30 days.");

      // "IP checks fails".
      case 'ip_address_check_fail':
      return t("We are sorry to inform you that you have not qualified for our community. There are many reasons why people don't qualify, which include, but may not be limited to, not fitting the screening criteria, not providing quality responses or other reasons defined by our partners.We highly recommend you try again in 30 days.");
       */
      // Terms.
      case 'terms_required':
        return t('Please agree to the all the Terms and Conditions to become a member of LifePoints.');

      // Access Denied.
      case 'access_denied':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Access Denied. You do not have enough privileges to access this page. Please try to log in.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // User not active.
      // IP - Address check fail.
      case 'user_not_active':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, your membership is temporarily unavailable. Please contact our Help Center for support.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // Verity check fail (when verity score = 1)
      case 'verity_check_fail':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, your membership is temporarily unavailable. Please contact our Help Center for support.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // "Token expired"
      case 'token_not_Availble':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, your login session is expired and we couldn't submit your request. Please try to log in again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'something_went_wrong':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Looks like something went wrong on our end. Please go back to the home page and try again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'update_password':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Your password has expired. Please update your password.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'login_required':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Access Denied. You do not have enough privileges to access this page. Please try to log in.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'survey_not_available':
        return t("No survey at this time. We will notify you when a new survey is available.");

      case 'survey_profiler_already_finish':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd It seems you have already finished this survey.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'survey_url_param_missing':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Not a valid Survey call, some parameters are missing.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'activity_not_available':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd It seems no activity is available.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'session_timeout':
      case 'panelist_session_timeout':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, the session ended because there was no activity and we were unable to submit your request. Try logging in again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'join_api_success':
        return t("OK");

      case 'join_api_missing_param_error':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, looks like something went wrong on our end. Please try again.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);
      case 'join_api_missing_param_error_new':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, this request could not be processed.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);
        

      // OP-6435.
      case 'email_empty_joinapi':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, this request could not be processed.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'join_api_invalid_email_error':
        return t("Invalid Email");

      case 'rewards_not_authorized':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd You are not authorized to redeem your reward points.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'survey_not_authorized':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd You are not authorized to take your survey", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'no_survey_text':
        return t('No study available');

      // Server timeout.
      case 'server_timeout':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd <p class='server_timeout'>Hmm… We can’t reach this page. The server is taking too long to respond and could not complete your request. Try to refresh the page or head back to the home page and try again.</p>", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // Reset password link expired.
      case 'reset_password_link_expire':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd The link has expired, please @linkexpirestart click here @linkexpireend to request a new email be sent.",
        ["@linkexpirestart" => t("<a href='" . Url::fromRoute('lp_login.forgetPassword')->toString() . "' class='forgot-your-password'>"), "@linkexpireend" => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'err_general_message_common':
        $config = \Drupal::service('settings');
        $configReportAProblemLink = $config->get('lp')['login']['ReportAProblemLink'];
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry! Looks like something went wrong on our end. Please try again later, and if the problem persists, contact our @linkhelpstart help desk. @linkhelpend", ["@linkhelpstart" => t("<a href='" . $configReportAProblemLink . "'>"), "@linkhelpend" => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'failed_imperium_check':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd We are sorry to inform you that you have not qualified for our community. There are many reasons why people don't qualify, which include, but may not be limited to, not fitting the screening criteria, not providing quality responses or other reasons defined by our partners.We highly recommend you try again in 30 days.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'cookies_not_available_for_panelist':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd No script running to drop/read/refresh any cookie", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'cookies_not_update':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Cookie not update due to no script running to drop/read/refresh any cookie", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'popupblocker':
        $config = \Drupal::service('settings');
        $configHelpPopupBlockerDesktopLink = $config->get('lp')['help']['PopupBlockerDesktopHelpLink'];
        $configHelpPopupBlockerSmartPhoneLink = $config->get('lp')['help']['PopupBlockerSmartPhoneHelpLink'];
        return t("To access surveys, please disable your popup blocker and refresh the page. Click for @blockerurldesktopstart desktop @blockerurldesktopend or @blockerurlsmartphonestart smartphone @blockerurlsmartphoneend instructions.", ["@blockerurldesktopstart" => t("<a href='" . $configHelpPopupBlockerDesktopLink . "' target='_blank'>"), "@blockerurldesktopend" => t("</a>"), "@blockerurlsmartphonestart" => t("<a href='" . $configHelpPopupBlockerSmartPhoneLink . "' target='_blank'>"), "@blockerurlsmartphoneend" => t("</a>")]);

      // Error message for DOI by Email.
      case 'ERR_DOI_ALREADY_DONE':
      case 'join_api_duplicate_error':
      case 'user_exist':
      case 'email_already_exists':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd There’s an account associated with your email address in our system, please sign in @loginherestart here @loginhereend or contact our Help Center for more information.", array("@loginherestart" => t("<string><a href='" . MappingUsages::recreateUrlWithLocaleForDefaultPanel('lp_login.Login') . "'>"), "@loginhereend" => t("</a></string>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")));
      case 'ERR_ACCESS_DENIED_INVALID_CREDENTIALS_DOI':
        return t("Access Denied. You do not have enough privileges to access this page.");
      // OP-6769.  
      case 'join_api_duplicate_error_new':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Sorry, this request could not be processed.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);
      case 'ERR_GENERAL_DOI':
        return t("The Account does not exist.");

      case 'account_closed':
        $config = \Drupal::service('settings');
        $configReportAProblemLink = $config->get('lp')['login']['ReportAProblemLink'];
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Your membership was canceled. Please @linkhelpstart contact us @linkhelpend if you have any questions.", ["@linkhelpstart" => t("<a href='" . $configReportAProblemLink . "'>"), "@linkhelpend" => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // "contact us test".
      case 'please_contact_us':
        $config = \Drupal::service('settings');
        $configHelpSupportLink = $config->get('lp')['login']['HelpSupportLink'];
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Please @supportlinkstart contact us @supportlinkend if you have any questions.", ["@supportlinkstart" => t("<a style='color:#FF5B00;' href='" . $configHelpSupportLink . "'>"), "@supportlinkend" => t("</a>"), '@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'failed_challenge_question':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd We are sorry to inform you that you have not qualified for our community. There are many reasons why people don't qualify, which include, but may not be limited to, not fitting the screening criteria, not providing quality responses or other reasons defined by our partners. Thank you for your interest.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // Parameter missing from UCM link.
      case 'ucm_link_param_missing':
        return t("Sorry, Invalid Consent URL. Some parameters are missing.");

      // OP-3642 error message.
      case 'consent_update_data_usage_preference_error':
        return t("Sorry, looks like something went wrong on our end while submitting the form. Please try again.");

      case 'cancel_now':
        return t("CANCEL NOW");

      // OP-5171 : User not found error message change for forget password page.
      case 'user_not_found_forgotpassword':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Email or password was entered incorrectly. If you have forgotten your password or email address, contact our Help Center for more information.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'wrong_existing_password':
        return t("Current Password is incorrect please try again.");

      case 'choose_another_email':
        return t("Please choose another email address.");
      // OP-5666 : added message for err_duplicate_ho_transaction_id type.
      case 'err_duplicate_ho_transaction_id':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd We are sorry to inform you that you have not qualified for our community. There are many reasons why people don't qualify, which include, but may not be limited to, not fitting the screening criteria, not providing quality responses or other reasons defined by our partners. We highly recommend you try again in 30 days.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // OP-6641 : added message for err_duplicate_ho_transaction_id type.
      case 'recruiter_eligible':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Prospective lead is not a member of our panel.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);
      case 'recruiter_not_eligible':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd Prospective lead is a member of our panel.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      case 'invalid_nucaptcha_entry':
        return t('Verification failed. Please try again.');

      default:
        return FALSE;
    }
  }

  /**
   * Get custom success messgae w.r.t field type.
   *
   * @param [string] $fieldType
   *   Field Type name.
   *
   * @return [string] success message code/prefix
   */
  public static function successMessageMapping($fieldType) {
    switch ($fieldType) {
      // "Poofile Change Passwrod submitting form"
      case 'change_password_submit':
        return t("Your password has been successfully changed.");

      // "Poofile while submitting form"
      case 'profile_submit':
        return t("Success! Your profile has been saved successfully.");

      case 'unsubscribe_submit':
        return t("Unsubscribe Successful and you have now cancelled your LifePoints membership. Please allow us a few days to update our records.");

      case 'forgot_password_mail_sent_submit':
        return t("@html1 ALL SET @html2 An email with a link to reset your password is now on its way. Check your inbox or SPAM/junk folder. @html3", ["@html1" => t("<div class='forgot_password_mail_sent_submit'><h3>"), "@html2" => t("</h3> <p>"), "@html3" => t("</p></div>")]);

      case 'activation_mail_sent_submit':
        return t("Please check your email. We've sent an email to your email address. Click the link in the email to activate your account.");

      case 'change_email_submit':
        return t("Thank you for updating your information. As a security measure, we require that you confirm your request to change your
      email address by clicking on the link that has been sent to you in your new email address.<br />
      If no email is received, or if you have any questions or concerns regarding this issue, please do not hesitate to contact us.");

      case 'terms_submit':
        return t("Thanks for your accpeting new updated term(s).");

      case 'take_first_survey_text':
        return t("Take your first survey and earn more LifePoints");

      // OP-3642 sucess message.
      case 'consent_update_data_usage_preference':
        return t("We successfully recorded your data preferences.");

      case 'consent_update_data_usage_preference_header':
        return t("Thanks for sharing!");
      
      // OP-7234
      case 'dob_submit':
          return t("Your profile has been updated, thank you!");
          
      case 'rewards_info':
          return t("Your bonus will be creditted to your account shortly and you will be updated by email");

      default:
        return TRUE;
    }
  }

  /**
   * Get custom warning messgae w.r.t field type.
   *
   * @param [string] $fieldType
   *   Field Type name.
   *
   * @return [string] warning message code/prefix
   */
  public static function warningMessageMapping($fieldType) {
    switch ($fieldType) {

      // Country Not Qualified.
      case 'country_not_qualified':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd<p class='country_not_qualified'>It looks like LifePoints is not available in your country yet. If you're interested in joining the LifePoints community, please contact our Help Center to request our survey panel in your country.</p>", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      // IP checks fails.
      case 'ip_check_fail':
      case 'ip_address_check_fail':
        $errorTitleText = CommonMessenger::errorMessageHeaderMapping($fieldType);
        return t("@titleHtmlStart @errorTitleText @titleHtmlEnd You have not qualified for our community. At this time, you don't fit our partners' screening criteria. We highly recommend you try again in 30 days.", ['@titleHtmlStart' => t("<h4 class='sr-only alert-msg-original-title'>"), '@errorTitleText' => $errorTitleText, '@titleHtmlEnd' => t("</h4>")]);

      default:
        return TRUE;
    }
  }

}
