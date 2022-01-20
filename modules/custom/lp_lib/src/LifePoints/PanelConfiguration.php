<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * Field Configuration as per panel.
 */
class PanelConfiguration {

  protected $panelData;
  /**
   * Country code.
   */
  protected $countryAbbrev;

  /**
   * First Name configuration variables.
   */

  protected $firstNameLabel;
  protected $firstNamePlaceHolder;

  /**
   * Last Name configuration variables.
   */

  protected $lastNameLabel;
  protected $lastNamePlaceHolder;

  /**
   * Email configuration variables.
   */

  protected $emailLabel;
  protected $emailPlaceHolder;

  /**
   * Confirm Email configuration variables.
   */

  protected $confirmEmailLabel;
  protected $confirmEmailPlaceHolder;

  /**
   * Password configuration variables.
   */

  protected $passwordLabel;
  protected $passwordPlaceHolder;

  /**
   * Confirm Password configuration variables.
   */

  protected $confirmPasswordLabel;
  protected $confirmPasswordPlaceHolder;

  /**
   * Gender configuration variables.
   */
  protected $genderFlag;
  protected $genderLabel;
  protected $genderSelectListFirst;
  protected $genderRequired;
  protected $genderOptions;

  /**
   * Date of birth configuration variables.
   */
  protected $dateofBirthLabel;
  protected $dateofBirthMonthLabel;
  protected $dateofBirthDayLabel;
  protected $dateofBirthYearLabel;
  protected $dateofBirthFormat;

  /**
   * Mailing address 1 variables.
   */
  protected $mailingAddress1Flag;
  protected $mailingAddress1Label;
  protected $mailingAddress1Placeholder;
  protected $mailingAddress1Required;

  /**
   * Mailing address 2 variables.
   */
  protected $mailingAddress2Flag;
  protected $mailingAddress2Label;
  protected $mailingAddress2Placeholder;
  protected $mailingAddress2Required;
  protected $availAgeLimit;
  protected $minAgeLimit;
  protected $maxAgeLimit;

  /**
   * State configuration variables.
   */
  protected $stateFlag;
  protected $stateLabel;
  protected $statePlaceholder;
  protected $stateListFirstOption;
  protected $stateRequired;
  protected $stateQuestionType;

  /**
   * City configuration variables.
   */
  protected $cityFlag;
  protected $cityLabel;
  protected $cityPlaceholder;
  protected $cityFirstOption;
  protected $cityRequired;
  protected $cityQuestionType;
  protected $showCityOtherField;

  /**
   * Postal code configuration variables.
   */
  protected $postalCodeFlag;
  protected $postalCodeLabel;
  protected $postalCodePlaceholder;
  protected $postalCodeRequired;

  /**
   * Current Password configuration variables.
   */
  protected $currentPasswordLabel;
  protected $currentPasswordPlaceHolder;

  /**
   * New email configuration variables.
   */
  protected $newEmailLabel;
  protected $newEmailPlaceHolder;

  /**
   * New password configuration variables.
   */
  protected $newPasswordLabel;
  protected $newPasswordPlaceHolder;

  /**
   * Confirm new password configuration variables.
   */
  protected $confirmNewPasswordLabel;
  protected $confirmNewPasswordPlaceHolder;

  /**
   * Username configuration variables.
   */

  protected $userNameLabel;

  /**
   * Panel Language.
   */
  protected $panelLanguage;
  /**
   * Accept Language.
   */
  protected $acceptLanguage;

  /**
   * Panel Redeemptions Progress Bar variables.
   */
  protected $currencyIcon;
  protected $redemptionTarget;
  protected $currencyValue;

  /**
   * Panel Privacy Advertising.
   */
  protected $privacyAdvertising;

  /**
   * Panel v3 reCaptcha reg form variable.
   */
  protected $v3CaptchaRegForm;

  /**
   * Panel v3 reCaptcha login form variable.
   */
  protected $v3CaptchaLoginForm;

  /**
   * Panel v3 reCaptcha forgot password form variable.
   */
  protected $v3CaptchaForgotForm;

  /**
   * Panel v2 reCaptcha reg form variable.
   */
  protected $v2CaptchaRegForm;

  /**
   * Panel v2 reCaptcha login form variable.
   */
  protected $v2CaptchaLoginForm;

  /**
   * Panel v2 reCaptcha forgot password form variable.
   */
  protected $v2CaptchaForgotForm;

  /**
   * Panel honeypot reg form variable.
   */
  protected $honeypotRegForm;

  /**
   * Panel honeypot login variable.
   */
  protected $honeypotLoginForm;

  /**
   * Panel honeypot forgot password variable.
   */
  protected $honeypotForgotForm;

  /**
   * Panel Consent banner settings.
   */
  protected $consentBanner;
  protected $consentBanProjectId;

  /**
   * Panel First profiler settings.
   */
  protected $firstProfiler;

  /**
   * UCM current version.
   */
  protected $ucmCurrentVersion;

  /**
   * Allow UCM.
   */
  protected $allowUCM;

  /**
   * Panel Cookies Cases settings.
   */
  protected $panelCookiesCases;

  /**
   * Panel App Banner Cases settings.
   */
  protected $appAndroidBanner;
  protected $appIosBanner;
  protected $bannerStartTime;
  protected $bannerEndTime;
  protected $bannerinfiniteTime;
  protected $dashboardAppBanner;
  protected $homePageAppIcon;

  /**
   * Panel Campaign Banner Cases settings.
   */
  protected $campaignBanner;
  protected $campaignBannerStartTime;
  protected $campaignBannerEndTime;

  /**
   * Panel Product Tour Cases settings.
   */
  protected $productTourForDesktop;
  protected $productTourForMobile;


  /**
   * Allow Survey Satisfaction Questions.
   */
  protected $surveySatisfactionQuestions;

  /**
   * Panel home page reward section settings.
   */
  protected $rewardSection;

  /**
   * Panel config switch off/on Fb banner.
   */
  protected $fbBannerCheck;

  /**
   * Allow NuDetect Integration.
   */
  protected $nuDetectIntegration;

  public function __construct() {
    $panel_code = MappingUsages::get_panel_code();
    if (!empty($panel_code)) {
      $this->setPanelConfiguration($panel_code);
    }
    else {
      $this->panelNotFound();
    }
    // OP-6638 - Text Input Fields translations displayed according to IP rather than locale folder
    // Get languageCode from URL.
    $languagecode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Get current default langauge.
    $default_languagecode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    // Modify panel language if only lang code detected. Add Current locale.
    $panelCode = (strcasecmp($languagecode, $default_languagecode) == 0) ? $this->getAcceptLanguage() : $languagecode;
    $this->setLabelAndPlaceholderConfiguration($panelCode);
  }

  /**
   * Function to set panel data configuration.
   */
  public function setPanelConfiguration($panel) {

    $properties = [];
    // Panel name.
    $properties['name'] = $panel;
    $properties['vid'] = 'lp_panels';
    // Get the panel data based on the property defined.
    $this->panelData = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->panelData = reset($this->panelData);

    // Check if field value not empty.
    if (!empty($this->panelData->field_lp_panel_available)) {
      $panelAvailable = $this->panelData->field_lp_panel_available->getValue();
      // Check panel available option check for new panel.
      $panelAvailable = $panelAvailable[0]['value'];
    }
    else {
      // Work to existing panel.
      $panelAvailable = TRUE;
    }
    if ($this->panelData && $panelAvailable) {

      // Setting up panel configurations.
      // Set country code (term id).
      $this->countryAbbrev = $this->panelData->field_lp_country_abbrev->getValue()[0]['target_id'];

      // Set gender configurations.
      $this->genderFlag = $this->panelData->field_lp_show_gender->getValue()[0]['value'];
      $this->genderRequired = $this->panelData->field_lp_gender_required->getValue()[0]['value'];
      $gender_allowed_value = $this->panelData->field_lp_gender_options->getSetting('allowed_values');
      // Build Panle prefered options.
      $gender_prefered_options = [];
      foreach ($this->panelData->field_lp_gender_options->getValue() as $key => $options) {
        $gender_prefered_options[$options['value']] = $gender_allowed_value[$options['value']];
      }
      $this->genderOptions = $gender_prefered_options;

      // Set date of birth configurations.
      if (isset($this->panelData->field_date_of_birth_format) && $this->panelData->field_date_of_birth_format->count()) {
        $this->dateofBirthFormat = $this->panelData->field_date_of_birth_format->getValue()[0]['value'];
      }

      // Set mailing address1 configurations.
      if (isset($this->panelData->field_lp_show_mailing_address_1) && $this->panelData->field_lp_show_mailing_address_1->count()) {
        $this->mailingAddress1Flag = $this->panelData->field_lp_show_mailing_address_1->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_mailing_address_1_req) && $this->panelData->field_lp_mailing_address_1_req->count()) {
        $this->mailingAddress1Required = $this->panelData->field_lp_mailing_address_1_req->getValue()[0]['value'];
      }

      // Set mailing address2 configurations.
      if (isset($this->panelData->field_lp_show_mailing_address_2) && $this->panelData->field_lp_show_mailing_address_2->count()) {
        $this->mailingAddress2Flag = $this->panelData->field_lp_show_mailing_address_2->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_mailing_address_2_req) && $this->panelData->field_lp_mailing_address_2_req->count()) {
        $this->mailingAddress2Required = $this->panelData->field_lp_mailing_address_2_req->getValue()[0]['value'];
      }

      // Set state configurations.
      if (isset($this->panelData->field_lp_show_state) && $this->panelData->field_lp_show_state->count()) {
        $this->stateFlag = $this->panelData->field_lp_show_state->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_state_list_first_option) && $this->panelData->field_state_list_first_option->count()) {
        $this->stateListFirstOption = $this->panelData->field_state_list_first_option->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_state_required) && $this->panelData->field_lp_state_required->count()) {
        $this->stateRequired = $this->panelData->field_lp_state_required->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_state_question_type) && $this->panelData->field_lp_state_question_type->count()) {
        $this->stateQuestionType = $this->panelData->field_lp_state_question_type->getValue()[0]['value'];
      }

      // Set city configurations.
      if (isset($this->panelData->field_lp_show_city) && $this->panelData->field_lp_show_city->count()) {
        $this->cityFlag = $this->panelData->field_lp_show_city->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_city_select_list_first_opt) && $this->panelData->field_city_select_list_first_opt->count()) {
        $this->cityFirstOption = $this->panelData->field_city_select_list_first_opt->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_city_required) && $this->panelData->field_lp_city_required->count()) {
        $this->cityRequired = $this->panelData->field_lp_city_required->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_city_question_type) && $this->panelData->field_lp_city_question_type->count()) {
        $this->cityQuestionType = $this->panelData->field_lp_city_question_type->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_show_city_other_field) && $this->panelData->field_show_city_other_field->count()) {
        $this->showCityOtherField = $this->panelData->field_show_city_other_field->getValue()[0]['value'];
      }

      // Set value for privacy advertising option.
      if (isset($this->panelData->field_lp_privacy_set_advertising) && $this->panelData->field_lp_privacy_set_advertising->count()) {
        $this->privacyAdvertising = $this->panelData->field_lp_privacy_set_advertising->getValue()[0]['value'];
      }

      // Set postal code configurations.
      if (isset($this->panelData->field_lp_show_postal_code) && $this->panelData->field_lp_show_postal_code->count()) {
        $this->postalCodeFlag = $this->panelData->field_lp_show_postal_code->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lp_postal_code_required) && $this->panelData->field_lp_postal_code_required->count()) {
        $this->postalCodeRequired = $this->panelData->field_lp_postal_code_required->getValue()[0]['value'];
      }

      // Set Age limit configurations.
      $this->availAgeLimitFlag = $this->panelData->field_lp_avail_age_limit->getValue()[0]['value'];
      $this->minAgeLimit = $this->panelData->field_lp_min_age_limit->getValue()[0]['value'];
      $this->maxAgeLimit = $this->panelData->field_lp_max_age_limit->getValue()[0]['value'];

      $this->panelLanguage = $this->panelData->field_lp_panel_language->getValue()[0]['value'];
      $this->acceptLanguage = $this->panelData->field_lp_accept_language->getValue()[0]['value'];
      // Set Redeemptions progress bar configurations.
      if (isset($this->panelData->field_lp_redeem_currency_icon)
        && $this->panelData->field_lp_redeem_currency_icon->count()
        && !empty($this->panelData->field_lp_redeem_currency_icon->getValue()[0]['target_id'])) {
        $this->currencyIcon = $this->panelData->field_lp_redeem_currency_icon->getValue()[0]['target_id'];
      }
      if (isset($this->panelData->field_lp_redemption_target)
        && $this->panelData->field_lp_redemption_target->count()
        && !empty($this->panelData->field_lp_redemption_target->getValue()[0]['value'])) {
        $this->redemptionTarget = $this->panelData->field_lp_redemption_target->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_redemption_currency_deci)
        && $this->panelData->field_redemption_currency_deci->count()
        && !empty($this->panelData->field_redemption_currency_deci->getValue()[0]['value'])) {
        $this->currencyValue = $this->panelData->field_redemption_currency_deci->getValue()[0]['value'];
      }

      // OP-6692 - Set v3 reCaptcha configuration.
      if (isset($this->panelData->field_v3_recaptcha_reg_form) && $this->panelData->field_v3_recaptcha_reg_form->count()) {
        $this->v3CaptchaRegForm = ($this->panelData->field_v3_recaptcha_reg_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_v3_recaptcha_login_form) && $this->panelData->field_v3_recaptcha_login_form->count()) {
        $this->v3CaptchaLoginForm = ($this->panelData->field_v3_recaptcha_login_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_v3_recaptcha_forgot_form) && $this->panelData->field_v3_recaptcha_forgot_form->count()) {
        $this->v3CaptchaForgotForm = ($this->panelData->field_v3_recaptcha_forgot_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }

      // OP-6692 - Set v2 reCaptcha configuration.
      if (isset($this->panelData->field_v2_recaptcha_reg_form) && $this->panelData->field_v2_recaptcha_reg_form->count()) {
        $this->v2CaptchaRegForm = ($this->panelData->field_v2_recaptcha_reg_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_v2_recaptcha_login_for) && $this->panelData->field_v2_recaptcha_login_for->count()) {
        $this->v2CaptchaLoginForm = ($this->panelData->field_v2_recaptcha_login_for->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_v2_recaptcha_forgot_form) && $this->panelData->field_v2_recaptcha_forgot_form->count()) {
        $this->v2CaptchaForgotForm = ($this->panelData->field_v2_recaptcha_forgot_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }

      // OP-6692 - Set honeypot configuration.
      if (isset($this->panelData->field_honeypot_reg_form) && $this->panelData->field_honeypot_reg_form->count()) {
        $this->honeypotRegForm = ($this->panelData->field_honeypot_reg_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_honeypot_login_form) && $this->panelData->field_honeypot_login_form->count()) {
        $this->honeypotLoginForm = ($this->panelData->field_honeypot_login_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      if (isset($this->panelData->field_honeypot_forgot_form) && $this->panelData->field_honeypot_forgot_form->count()) {
        $this->honeypotForgotForm = ($this->panelData->field_honeypot_forgot_form->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }

      // Set Panel consent banner configuration variable.
      if (isset($this->panelData->field_lp_consent_banner_config)
        && $this->panelData->field_lp_consent_banner_config->count()
        && !empty($this->panelData->field_lp_consent_banner_config->getValue()[0]['value'])) {
        $this->consentBanner = $this->panelData->field_lp_consent_banner_config->getValue()[0]['value'];
      }
      // Set UCM configuration.
      if (isset($this->panelData->field_allow_ucm) && $this->panelData->field_allow_ucm->count()) {
        $this->allowUCM = ($this->panelData->field_allow_ucm->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }

      if (isset($this->panelData->field_lp_consent_banner_pro_id)
        && $this->panelData->field_lp_consent_banner_pro_id->count()
        && !empty($this->panelData->field_lp_consent_banner_pro_id->getValue()[0]['value'])) {
        $this->consentBanProjectId = $this->panelData->field_lp_consent_banner_pro_id->getValue()[0]['value'];
      }

      // Set Panel first profiler configuration variable.
      if (isset($this->panelData->field_skip_first_profiler_finish)
        && $this->panelData->field_skip_first_profiler_finish->count()
        && !empty($this->panelData->field_skip_first_profiler_finish->getValue()[0]['value'])) {
        $this->firstProfiler = $this->panelData->field_skip_first_profiler_finish->getValue()[0]['value'];
      }

      // OP-4505 - Set value for Cookies Cases.
      if (isset($this->panelData->field_lp_cookies_cases) && $this->panelData->field_lp_cookies_cases->count()) {
        foreach ($this->panelData->field_lp_cookies_cases->getValue() as $key => $options) {
          $this->panelCookiesCases[] = $options['target_id'];
        }
      }

      // OP-4643 - Set value for ucm current version.
      if (isset($this->panelData->field_lp_panel_ucm_version)
        && $this->panelData->field_lp_panel_ucm_version->count()
        && !empty($this->panelData->field_lp_panel_ucm_version->getValue()[0]['value'])) {
        $this->ucmCurrentVersion = $this->panelData->field_lp_panel_ucm_version->getValue()[0]['value'];
      }
      // OP-5120 - Set value for App banner config fields.
      if (isset($this->panelData->field_android_app_banner)
      && $this->panelData->field_android_app_banner->count()
      && !empty($this->panelData->field_android_app_banner->getValue()[0]['value'])) {
        $this->appAndroidBanner = $this->panelData->field_android_app_banner->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_ios_app_banner)
      && $this->panelData->field_ios_app_banner->count()
      && !empty($this->panelData->field_ios_app_banner->getValue()[0]['value'])) {
        $this->appIosBanner = $this->panelData->field_ios_app_banner->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_app_banner_start_time)
      && $this->panelData->field_app_banner_start_time->count()
      && !empty($this->panelData->field_app_banner_start_time->getValue()[0]['value'])) {
        $this->bannerStartTime = $this->panelData->field_app_banner_start_time->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_app_banner_end_time)
      && $this->panelData->field_app_banner_end_time->count()
      && !empty($this->panelData->field_app_banner_end_time->getValue()[0]['value'])) {
        $this->bannerEndTime = $this->panelData->field_app_banner_end_time->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_app_banner_for_infinite)
      && $this->panelData->field_app_banner_for_infinite->count()
      && !empty($this->panelData->field_app_banner_for_infinite->getValue()[0]['value'])) {
        $this->bannerinfiniteTime = $this->panelData->field_app_banner_for_infinite->getValue()[0]['value'];
      }
      // OP-6761.
      if (isset($this->panelData->field_dashboard_app_banner)
      && $this->panelData->field_dashboard_app_banner->count()
      && !empty($this->panelData->field_dashboard_app_banner->getValue()[0]['value'])) {
        $this->dashboardAppBanner = $this->panelData->field_dashboard_app_banner->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_home_page_app_icons)
      && $this->panelData->field_home_page_app_icons->count()
      && !empty($this->panelData->field_home_page_app_icons->getValue()[0]['value'])) {
        $this->homePageAppIcon = $this->panelData->field_home_page_app_icons->getValue()[0]['value'];
      }

      // OP-6345.
      if (isset($this->panelData->field_enable_campaign_banner)
      && $this->panelData->field_enable_campaign_banner->count()
      && !empty($this->panelData->field_enable_campaign_banner->getValue()[0]['value'])) {
        $this->campaignBanner = $this->panelData->field_enable_campaign_banner->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_campaign_start_time)
      && $this->panelData->field_campaign_start_time->count()
      && !empty($this->panelData->field_campaign_start_time->getValue()[0]['value'])) {
        $this->campaignBannerStartTime = $this->panelData->field_campaign_start_time->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_campaign_end_time)
      && $this->panelData->field_campaign_end_time->count()
      && !empty($this->panelData->field_campaign_end_time->getValue()[0]['value'])) {
        $this->campaignBannerEndTime = $this->panelData->field_campaign_end_time->getValue()[0]['value'];
      }
      // OP-6445.
      if (isset($this->panelData->field_allow_product_tour_desktop)
      && $this->panelData->field_allow_product_tour_desktop->count()
      && !empty($this->panelData->field_allow_product_tour_desktop->getValue()[0]['value'])) {
        $this->productTourForDesktop = $this->panelData->field_allow_product_tour_desktop->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_allow_product_tour_mobile)
      && $this->panelData->field_allow_product_tour_mobile->count()
      && !empty($this->panelData->field_allow_product_tour_mobile->getValue()[0]['value'])) {
        $this->productTourForMobile = $this->panelData->field_allow_product_tour_mobile->getValue()[0]['value'];
      }

      // Set value for surveySatisfactionQuestions.
      if (isset($this->panelData->field_survey_satisfaction_ques) && $this->panelData->field_survey_satisfaction_ques->count()) {
        $this->surveySatisfactionQuestions = ($this->panelData->field_survey_satisfaction_ques->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }
      // OP-5992.
      if (isset($this->panelData->field_home_page_rewards_section)
      && $this->panelData->field_home_page_rewards_section->count()
      && !empty($this->panelData->field_home_page_rewards_section->getValue()[0]['value'])) {
        $this->rewardSection = $this->panelData->field_home_page_rewards_section->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_facebook_banner_kcm)
      && $this->panelData->field_facebook_banner_kcm->count()
      && !empty($this->panelData->field_facebook_banner_kcm->getValue()[0]['value'])) {
        $this->fbBannerCheck = $this->panelData->field_facebook_banner_kcm->getValue()[0]['value'];
      }

      // Set value for nuDetectIntegration.
      if (isset($this->panelData->field_nudetect_integration) && $this->panelData->field_nudetect_integration->count()) {
        $this->nuDetectIntegration = ($this->panelData->field_nudetect_integration->getValue()[0]['value'] == TRUE) ? TRUE : FALSE;
      }

    }
    else {
      $this->panelNotFound();
    }
  }

  /**
   * Function to set Label And Placeholder data configuration.
   */
  public function setLabelAndPlaceholderConfiguration($panel) {
    // OP-6638 - Text Input Fields translations displayed according to IP rather than locale folder.
    $panel = explode("-", $panel);
    $panelCountry = isset($panel[1]) ? strtoupper($panel[1]) : '';
    $panelLanguage = isset($panel[0]) ? strtoupper($panel[0]) : '';
    $panelCode = $panelCountry . '_' . $panelLanguage;

    $properties = [];
    // Panel name.
    $properties['name'] = $panelCode;
    $properties['vid'] = 'lp_panels';
    // Get the panel data based on the property defined.
    $this->panelData = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->panelData = reset($this->panelData);

    // Check if field value not empty.
    if (!empty($this->panelData->field_lp_panel_available)) {
      $panelAvailable = $this->panelData->field_lp_panel_available->getValue();
      // Check panel available option check for new panel.
      $panelAvailable = $panelAvailable[0]['value'];
    }
    else {
      // Work to existing panel.
      $panelAvailable = TRUE;
    }

    if ($this->panelData && $panelAvailable) {
      // Set username configurations.
      if (isset($this->panelData->field_username_label_login) && $this->panelData->field_username_label_login->count()) {
        $this->userNameLabel = $this->panelData->field_username_label_login->getValue()[0]['value'];
      }

      if (isset($this->panelData->field_email_address_placeholder) && $this->panelData->field_email_address_placeholder->count()) {
        $this->emailPlaceHolder = $this->panelData->field_email_address_placeholder->getValue()[0]['value'];
      }

      // Set password configurations.
      if (isset($this->panelData->field_password_label) && $this->panelData->field_password_label->count()) {
        $this->passwordLabel = $this->panelData->field_password_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_password_placeholder) && $this->panelData->field_password_placeholder->count()) {
        $this->passwordPlaceHolder = $this->panelData->field_password_placeholder->getValue()[0]['value'];
      }

      // Set first name configurations.
      if (isset($this->panelData->field_firstname_label) && $this->panelData->field_firstname_label->count()) {
        $this->firstNameLabel = $this->panelData->field_firstname_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_firstname_placeholder) && $this->panelData->field_firstname_placeholder->count()) {
        $this->firstNamePlaceHolder = $this->panelData->field_firstname_placeholder->getValue()[0]['value'];
      }

      // Set last name configurations.
      if (isset($this->panelData->field_lastname_label) && $this->panelData->field_lastname_label->count()) {
        $this->lastNameLabel = $this->panelData->field_lastname_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_lastname_placeholder) && $this->panelData->field_lastname_placeholder->count()) {
        $this->lastNamePlaceHolder = $this->panelData->field_lastname_placeholder->getValue()[0]['value'];
      }

      // Set email configurations.
      if (isset($this->panelData->field_email_address_label) && $this->panelData->field_email_address_label->count()) {
        $this->emailLabel = $this->panelData->field_email_address_label->getValue()[0]['value'];
      }

      // Set confirm email configurations.
      if (isset($this->panelData->field_confirm_email_add_label) && $this->panelData->field_confirm_email_add_label->count()) {
        $this->confirmEmailLabel = $this->panelData->field_confirm_email_add_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_confirm_email_add_placehol) && $this->panelData->field_confirm_email_add_placehol->count()) {
        $this->confirmEmailPlaceHolder = $this->panelData->field_confirm_email_add_placehol->getValue()[0]['value'];
      }

      // Set confirm password configurations.
      if (isset($this->panelData->field_confirm_password_label) && $this->panelData->field_confirm_password_label->count()) {
        $this->confirmPasswordLabel = $this->panelData->field_confirm_password_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_confirm_password_placehold) && $this->panelData->field_confirm_password_placehold->count()) {
        $this->confirmPasswordPlaceHolder = $this->panelData->field_confirm_password_placehold->getValue()[0]['value'];
      }

      // Set gender field configurations.
      $this->genderLabel = $this->panelData->field_lp_gender_field_label->getValue()[0]['value'];
      if (isset($this->panelData->field_gender_select_list_first) && $this->panelData->field_gender_select_list_first->count()) {
        $this->genderSelectListFirst = $this->panelData->field_gender_select_list_first->getValue()[0]['value'];
      }

      // Set date of birth configurations.
      if (isset($this->panelData->field_date_of_birth_label) && $this->panelData->field_date_of_birth_label->count()) {
        $this->dateofBirthLabel = $this->panelData->field_date_of_birth_label->getValue()[0]['value'];
      }

      // Set date of birth Month configurations.
      if (isset($this->panelData->field_date_of_birth_month_label) && $this->panelData->field_date_of_birth_month_label->count()) {
        $this->dateofBirthMonthLabel = $this->panelData->field_date_of_birth_month_label->getValue()[0]['value'];
      }

      // Set date of birth day configurations.
      if (isset($this->panelData->field_date_of_birth_day_label) && $this->panelData->field_date_of_birth_day_label->count()) {
        $this->dateofBirthDayLabel = $this->panelData->field_date_of_birth_day_label->getValue()[0]['value'];
      }

      // Set date of birth year configurations.
      if (isset($this->panelData->field_date_of_birth_year_label) && $this->panelData->field_date_of_birth_year_label->count()) {
        $this->dateofBirthYearLabel = $this->panelData->field_date_of_birth_year_label->getValue()[0]['value'];
      }

      // Set Mailing Address 1 configurations.
      if (isset($this->panelData->field_lp_mailing_address_1_label) && $this->panelData->field_lp_mailing_address_1_label->count()) {
        $this->mailingAddress1Label = $this->panelData->field_lp_mailing_address_1_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_mailing_address_1_placehol) && $this->panelData->field_mailing_address_1_placehol->count()) {
        $this->mailingAddress1Placeholder = $this->panelData->field_mailing_address_1_placehol->getValue()[0]['value'];
      }

      // Set Mailing Address 2 configurations.
      if (isset($this->panelData->field_lp_mailing_address_2_label) && $this->panelData->field_lp_mailing_address_2_label->count()) {
        $this->mailingAddress2Label = $this->panelData->field_lp_mailing_address_2_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_mailing_address_2_placehol) && $this->panelData->field_mailing_address_2_placehol->count()) {
        $this->mailingAddress2Placeholder = $this->panelData->field_mailing_address_2_placehol->getValue()[0]['value'];
      }

      // Set state configurations.
      if (isset($this->panelData->field_lp_state_label) && $this->panelData->field_lp_state_label->count()) {
        $this->stateLabel = $this->panelData->field_lp_state_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_state_placeholder) && $this->panelData->field_state_placeholder->count()) {
        $this->statePlaceholder = $this->panelData->field_state_placeholder->getValue()[0]['value'];
      }

      // Set city configurations.
      if (isset($this->panelData->field_lp_city_label) && $this->panelData->field_lp_city_label->count()) {
        $this->cityLabel = $this->panelData->field_lp_city_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_city_placeholder) && $this->panelData->field_city_placeholder->count()) {
        $this->cityPlaceholder = $this->panelData->field_city_placeholder->getValue()[0]['value'];
      }

      // Set postal code configurations.
      if (isset($this->panelData->field_lp_postal_code_label) && $this->panelData->field_lp_postal_code_label->count()) {
        $this->postalCodeLabel = $this->panelData->field_lp_postal_code_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_postal) && $this->panelData->field_postal->count()) {
        $this->postalCodePlaceholder = $this->panelData->field_postal->getValue()[0]['value'];
      }

      // Set current password configurations.
      if (isset($this->panelData->field_current_password_label) && $this->panelData->field_current_password_label->count()) {
        $this->currentPasswordLabel = $this->panelData->field_current_password_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_current_password_placehold) && $this->panelData->field_current_password_placehold->count()) {
        $this->currentPasswordPlaceHolder = $this->panelData->field_current_password_placehold->getValue()[0]['value'];
      }

      // Set new email configurations.
      if (isset($this->panelData->field_new_email_address_label) && $this->panelData->field_new_email_address_label->count()) {
        $this->newEmailLabel = $this->panelData->field_new_email_address_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_new_email_add_placeholder) && $this->panelData->field_new_email_add_placeholder->count()) {
        $this->newEmailPlaceHolder = $this->panelData->field_new_email_add_placeholder->getValue()[0]['value'];
      }

      // Set new password configurations.
      if (isset($this->panelData->field_new_password_label) && $this->panelData->field_new_password_label->count()) {
        $this->newPasswordLabel = $this->panelData->field_new_password_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_new_password) && $this->panelData->field_new_password->count()) {
        $this->newPasswordPlaceHolder = $this->panelData->field_new_password->getValue()[0]['value'];
      }

      // Set confirm new password configurations.
      if (isset($this->panelData->field_confirm_new_password_label) && $this->panelData->field_confirm_new_password_label->count()) {
        $this->confirmNewPasswordLabel = $this->panelData->field_confirm_new_password_label->getValue()[0]['value'];
      }
      if (isset($this->panelData->field_confirm_new_pwd_placeholde) && $this->panelData->field_confirm_new_pwd_placeholde->count()) {
        $this->confirmNewPasswordPlaceHolder = $this->panelData->field_confirm_new_pwd_placeholde->getValue()[0]['value'];
      }

    }
  }

  /**
   * If Panel not found show error.
   */
  public function panelNotFound() {
    $routeName = \Drupal::routeMatch()->getRouteName();
    if ($routeName === 'user.login'
      || count(\Drupal::currentUser()->getRoles()) > 1
      || \Drupal::request()->query->get('__func') === 'api'
      || strncmp($routeName, 'jsonapi.', 7) === 0
      || strncmp($routeName, 'lp_geoip_lookup.', 16) === 0
      || $routeName === 'lp_jsonrpc.handler'
      || $routeName === 'lp_unsubscribe.unsubscribeByEmail'
      || $routeName === 'lp_curiosity_question.curiosity_config_js'
      || $routeName === 'lp_override_locale.locale_override') {
      // We don't need to redirect user in case of Drupal login/role.
      // We don't need to redirect user in case of join api or jsonapi flow.
    }
    else {
      $browserLang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : NULL;
      // OP-4941 We don't need to log this entry as its not needed now.
      // Enable to Test India Panel issue. Can disable after some time.
      $logMsg = '<pre><code>Panel [CountryCode_LanguageCode]: <strong>' . MappingUsages::get_panel_code() . '</strong><br>client IP Address: <strong>' . MappingUsages::get_client_ip_address() . '</strong><br>Browser Language: "' . $browserLang . '<br>Error Machine name: "country_not_qualified"' . '<br>Error Display Message: "' . CommonMessenger::errorMessageMapping("country_not_qualified") . '"</br></code></pre>';
      // Adding Logs Data for Panel error.
      $logMsg .= print_r($_SERVER, TRUE);
      \Drupal::logger('LifePoints Portal')->notice($logMsg);

      \Drupal::messenger()->addWarning(CommonMessenger::warningMessageMapping("country_not_qualified"));
      // Set redirection.
      // OP-5849 - Allow to access Content pages(community, Who we are etc..) without accept language.
      // Login/Registration is not allow to access without accept language.
      if ($routeName == 'lp_login.Login' || $routeName == 'lp_registration.registrationForm') {
        $homeUrl = Url::fromRoute('<front>');
        $response = new RedirectResponse($homeUrl->toString());
        $response->send();
      }
    }
  }

  /**
   * Getter for panel Language.
   */
  public function getPanelLanguage() {
    return $this->panelLanguage;
  }

  /**
   * Getter for Accept Language.
   */
  public function getAcceptLanguage() {
    return $this->acceptLanguage;
  }

  /**
   * Getter for V3Captcha.
   */
  public function getV3Captcha() {
    return [
      'v3RegForm' => $this->v3CaptchaRegForm,
      'v3LoginForm' => $this->v3CaptchaLoginForm,
      'v3ForgotForm' => $this->v3CaptchaForgotForm,
    ];
  }

  /**
   * Getter for V2Captcha.
   */
  public function getV2Captcha() {
    return [
      'v2RegForm' => $this->v2CaptchaRegForm,
      'v2LoginForm' => $this->v2CaptchaLoginForm,
      'v2ForgotForm' => $this->v2CaptchaForgotForm,
    ];
  }

  /**
   * Getter for Honeypot.
   */
  public function getHoneypot() {
    return [
      'honeypotRegForm' => $this->honeypotRegForm,
      'honeypotLoginForm' => $this->honeypotLoginForm,
      'honeypotForgotForm' => $this->honeypotForgotForm,
    ];
  }

  /**
   * Getter for country code.
   */
  public function getCountryAbbrev() {
    return $this->countryAbbrev;
  }

  /**
   * Getter for first name field configurations.
   */
  public function getFirstNameConfiguration() {
    return [
      'label' => $this->firstNameLabel,
      'placeholder' => $this->firstNamePlaceHolder,
    ];
  }

  /**
   * Getter for last name field configurations.
   */
  public function getLastNameConfiguration() {
    return [
      'label' => $this->lastNameLabel,
      'placeholder' => $this->lastNamePlaceHolder,
    ];
  }

  /**
   * Getter for email field configurations.
   */
  public function getEmailConfiguration() {
    return [
      'label' => $this->emailLabel,
      'placeholder' => $this->emailPlaceHolder,
    ];
  }

  /**
   * Getter for confirm email field configurations.
   */
  public function getConfirmEmailConfiguration() {
    return [
      'label' => $this->confirmEmailLabel,
      'placeholder' => $this->confirmEmailPlaceHolder,
    ];
  }

  /**
   * Getter for password field configurations.
   */
  public function getPasswordConfiguration() {
    return [
      'label' => $this->passwordLabel,
      'placeholder' => $this->passwordPlaceHolder,
    ];
  }

  /**
   * Getter for confirm password field configurations.
   */
  public function getConfirmPasswordConfiguration() {
    return [
      'label' => $this->confirmPasswordLabel,
      'placeholder' => $this->confirmPasswordPlaceHolder,
    ];
  }

  /**
   * Getter for gender field configurations.
   */
  public function getGenderConfiguration() {
    return [
      'flag' => $this->genderFlag,
      'label' => $this->genderLabel,
      'genderSelectListFirst' => $this->genderSelectListFirst,
      'required' => $this->genderRequired,
      'options' => $this->genderOptions,
    ];
  }

  /**
   * Getter for date of birth field configurations.
   */
  public function getDateofBirthConfiguration() {
    return [
      'label' => $this->dateofBirthLabel,
      'monthlabel' => $this->dateofBirthMonthLabel,
      'daylabel' => $this->dateofBirthDayLabel,
      'yearlabel' => $this->dateofBirthYearLabel,
      'format' => $this->dateofBirthFormat,
    ];
  }

  /**
   * Getter for mailing address 1 field configurations.
   */
  public function getMailingAddress1Configuration() {
    return [
      'flag' => $this->mailingAddress1Flag,
      'label' => $this->mailingAddress1Label,
      'placeholder' => $this->mailingAddress1Placeholder,
      'required' => $this->mailingAddress1Required,
    ];
  }

  /**
   * Getter for mailing address 2 field configurations.
   */
  public function getMailingAddress2Configuration() {
    return [
      'flag' => $this->mailingAddress2Flag,
      'label' => $this->mailingAddress2Label,
      'placeholder' => $this->mailingAddress2Placeholder,
      'required' => $this->mailingAddress2Required,
    ];
  }

  /**
   * Getter for state field configurations.
   */
  public function getStateConfiguration() {
    return [
      'flag' => $this->stateFlag,
      'label' => $this->stateLabel,
      'placeholder' => $this->statePlaceholder,
      'stateListFirstOption' => $this->stateListFirstOption,
      'required' => $this->stateRequired,
      'type' => $this->stateQuestionType,
    ];
  }

  /**
   * Getter for city field configurations.
   */
  public function getCityConfiguration() {
    return [
      'flag' => $this->cityFlag,
      'label' => $this->cityLabel,
      'placeholder' => $this->cityPlaceholder,
      'cityFirstOption' => $this->cityFirstOption,
      'required' => $this->cityRequired,
      'type' => $this->cityQuestionType,
      'cityOther' => $this->showCityOtherField,
    ];
  }

  /**
   * Getter for postal code field configurations.
   */
  public function getPostalCodeConfiguration() {
    return [
      'flag' => $this->postalCodeFlag,
      'label' => $this->postalCodeLabel,
      'placeholder' => $this->postalCodePlaceholder,
      'required' => $this->postalCodeRequired,
    ];
  }

  /**
   * Getter for postal code field configurations.
   */
  public function getUsernameConfiguration() {
    return [
      'label' => $this->userNameLabel,
      'placeholder' => $this->emailPlaceHolder,
    ];
  }

  /**
   * Getter for current password field configurations.
   */
  public function getCurrentPasswordConfiguration() {
    return [
      'label' => $this->currentPasswordLabel,
      'placeholder' => $this->currentPasswordPlaceHolder,
    ];
  }

  /**
   * Getter for new email field configurations.
   */
  public function getNewEmailConfiguration() {
    return [
      'label' => $this->newEmailLabel,
      'placeholder' => $this->newEmailPlaceHolder,
    ];
  }

  /**
   * Getter for new password field configurations.
   */
  public function getNewPasswordConfiguration() {
    return [
      'label' => $this->newPasswordLabel,
      'placeholder' => $this->newPasswordPlaceHolder,
    ];
  }

  /**
   * Getter for confirm new password field configurations.
   */
  public function getConfirmNewPasswordConfiguration() {
    return [
      'label' => $this->confirmNewPasswordLabel,
      'placeholder' => $this->confirmNewPasswordPlaceHolder,
    ];
  }

  /**
   * Function to get state options based on the country code.
   */
  public function getStateOptions($country_abbrev) {
    $properties = [];
    // Get Drupal Language set.
    $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // Setting up the properties.
    $properties['field_lp_country_abbrev'] = $country_abbrev;
    $properties['vid'] = 'lp_state';
    // Load the state taxonomy based on country.
    $state_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $states = [];
    foreach ($state_data as $tid => $data) {
      // Check translation.
      if ($data->hasTranslation($drupal_language)) {
        $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($data, $drupal_language);
        $term_name = $translated_term->getName();
      }
      else {
        $term_name = $data->getName();
      }
      $states[$tid] = $term_name;
    }
    asort($states);
    return $states;
  }

  /**
   * Get city value with state local code tid.
   */
  public function getCityByStateLocalCodeTid($state_localcode_tid) {
    if ($state_localcode_tid) {
      $properties = [];
      // Get Drupal Language set.
      $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      // Setting up the properties.
      $properties['field_lp_state_local_code'] = $state_localcode_tid;
      $properties['vid'] = 'lp_city';
      // Load the city data based on properties.
      $city_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $city = [];
      foreach ($city_data as $tid => $data) {
        // Check translation.
        if ($data->hasTranslation($drupal_language)) {
          $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($data, $drupal_language);
          $term_name = $translated_term->getName();
        }
        else {
          $term_name = $data->getName();
        }
        $city[$tid] = $term_name;
      }
      return $city;
    }
    else {
      return [];
    }
  }

  /**
   * Function to get city by state local code.
   */
  public function getCityByStateLocalCode($state_term_id) {
    if ($state_term_id) {
      $properties = [];
      // Get Drupal Language set.
      $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      // Setting up the properties.
      $properties['vid'] = 'lp_state';
      $properties['tid'] = $state_term_id;
      // Load the state taxonomy based on properties.
      $state_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $state_data = reset($state_data);
      // Fix OP-2714.
      if (empty($state_data)) {
        return [];
      }
      if ($state_data->hasTranslation($drupal_language)) {
        $state_translated = \Drupal::service('entity.repository')->getTranslationFromContext($state_data, $drupal_language);
        $state_local_code = $state_translated->field_lp_state_local_code->getValue()[0]['target_id'];
      }
      else {
        $state_local_code = $state_data->field_lp_state_local_code->getValue()[0]['target_id'];
      }
      $properties = [];
      // Setting up the properties.
      $properties['field_lp_state_local_code'] = $state_local_code;
      $properties['vid'] = 'lp_city';

      // Load the city data based on properties.
      $city_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      $city = [];
      foreach ($city_data as $tid => $data) {
        // Check translation.
        if ($data->hasTranslation($drupal_language)) {
          $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($data, $drupal_language);
          $term_name = $translated_term->getName();
        }
        else {
          $term_name = $data->getName();
        }
        $city[$tid] = $term_name;
      }
      asort($city);
      return $city;
    }
    else {
      return [];
    }
  }

  /**
   * Getter for age limit field configurations.
   */
  public function getAgeLimitConfiguration() {
    return [
      'flag' => $this->availAgeLimitFlag,
      'minAgeLimit' => $this->minAgeLimit,
      'maxAgeLimit' => $this->maxAgeLimit,
    ];
  }

  /**
   * Getter for progress bar fields currency and redemption configurations.
   */
  public function getProgressBarConfiguration() {
    return [
      'redemptionTarget' => $this->redemptionTarget,
      'currencyValue' => $this->currencyValue,
      'currencyIcon' => $this->currencyIcon,
    ];
  }

  /**
   * Getter for Privacy Advertising field configurations.
   */
  public function getPrivacyAdvertisingConfiguration() {
    return [
      'privacyAdvertising' => $this->privacyAdvertising,
    ];
  }

  /**
   * Getter for conset banner field configurations.
   */
  public function getConsentBannerConfiguration() {
    return [
      'consentBanner' => $this->consentBanner,
      'consentBanProjectId' => $this->consentBanProjectId,
    ];
  }

  /**
   * Getter for first profiler field configurations.
   */
  public function getFirstProfilerConfiguration() {
    return [
      'firstProfiler' => $this->firstProfiler,
    ];
  }

  /**
   * Getter for allow UCM.
   */
  public function getAllowUCM() {
    return $this->allowUCM;
  }

  /**
   * OP-4505 - Getter for Cookies Cases field configurations.
   */
  public function getCookiesCasesConfiguration() {
    return [
      'panelCookiesCases' => $this->panelCookiesCases,
    ];
  }

  /**
   * OP-4643 - Getter for UCM current version.
   */
  public function getUcmCurrentVesrion() {
    return $this->ucmCurrentVersion;
  }

  /**
   * Getter for app banner field configurations.
   */
  public function getAppBannerConfiguration() {
    return [
      'appAndroidBanner' => $this->appAndroidBanner,
      'appIosBanner' => $this->appIosBanner,
      'bannerStartTime' => $this->bannerStartTime,
      'bannerEndTime' => $this->bannerEndTime,
      'bannerinfiniteTime' => $this->bannerinfiniteTime,
      'dashboardAppBanner' => $this->dashboardAppBanner,
      'homePageAppIcon' => $this->homePageAppIcon,
    ];
  }

  /**
   * Getter for app banner field configurations.
   */
  public function getCampaignBannerConfiguration() {
    return [
      'campaignBanner' => $this->campaignBanner,
      'campaignBannerStartTime' => $this->campaignBannerStartTime,
      'campaignBannerEndTime' => $this->campaignBannerEndTime,
    ];
  }

  /**
   * Getter for product tour field configurations.
   */
  public function getProductTourConfiguration() {
    return [
      'productTourForDesktop' => $this->productTourForDesktop,
      'productTourForMobile' => $this->productTourForMobile,
    ];
  }

  /**
   * OP-5734: SHS (Portal): UI ticket for the SSQ model box / form.
   * Getter function to show survey satisfaction questions field configurations.
   */
  public function getSurveySatisfactionQuestionsConfiguration() {
    return $this->surveySatisfactionQuestions;
  }

  /**
   * OP-5992 - Getter for Home page reward section configurations.
   */
  public function getRewardSectionSettings() {
    return $this->rewardSection;
  }

  /**
   * OP-6577 - Getter for FB banner section configurations.
   */
  public function fbBannerCheckConfig() {
    return $this->fbBannerCheck;
  }

  /**
   * OP-6727: Getter for NuDetect Integration configurations.
   */
  public function getNuDetectIntegrationConfiguration() {
    return $this->nuDetectIntegration;
  }

}
