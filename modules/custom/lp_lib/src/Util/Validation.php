<?php

namespace Drupal\lp_lib\Util;

use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;

/**
 * Validation use for global validation.
 */
class Validation {
  /**
   * Constructor for setting the required service object.
   */
  public function __construct() {
  }

  /**
   * Global Function to check the validation.
   */
  public function fieldValidate($field_name, &$form_state) {
    $error_msg = "";
    $validationErrorMsg = [];
    // Return if field value is empty.
    if ($field_name == 'city_other' && empty($form_state->getValue($field_name))) {
      $error_msg .= CommonMessenger::errorMessageMapping("city_other_required");
      $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "city_other_required", $validationErrorMsg);
    }
    elseif (empty($form_state->getValue($field_name))) {
      return;
    }

    switch ($field_name) {
      // Validation for term field.
      case 'terms':
        if ($form_state->getValue($field_name) != 1) {
          $error_msg .= CommonMessenger::errorMessageMapping("term_required");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "term_required", $validationErrorMsg);
        }
        break;

      case 'first_name':
      case 'last_name':
        if (!$this->validateName($form_state->getValue($field_name))) {
          $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_regex");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_regex", $validationErrorMsg);
        }
        if ($this->validateLength($form_state->getValue($field_name), 1, '<')) {
          $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_short");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_short", $validationErrorMsg);
        }
        if ($this->validateLength($form_state->getValue($field_name), 30, '>')) {
          $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_long");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_long", $validationErrorMsg);
        }
        break;

      case 'email_address':
      case 'new_email_address':
        if (!$this->validateEmail($form_state->getValue($field_name))) {
          $error_msg .= CommonMessenger::errorMessageMapping("email_invalid");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "email_invalid", $validationErrorMsg);
        }
        if ($this->validateLength($form_state->getValue($field_name), 50, '>')) {
          $error_msg .= CommonMessenger::errorMessageMapping("email_long");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "email_long", $validationErrorMsg);
        }
        break;

      case 'confirm_email_address':
        if ($form_state->getValue('email_address') != $form_state->getValue('confirm_email_address')) {
          $error_msg .= CommonMessenger::errorMessageMapping("email_match");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "email_match", $validationErrorMsg);
        }
        break;

      case 'password':
        if (!$this->validatePassword($form_state->getValue($field_name))) {
          $error_msg .= CommonMessenger::errorMessageMapping("password_regex");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "password_regex", $validationErrorMsg);
        }
        if ($this->validateLength($form_state->getValue($field_name), 6, '<')) {
          $error_msg .= CommonMessenger::errorMessageMapping("password_short");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "password_short", $validationErrorMsg);
        }
        if ($this->validateLength($form_state->getValue($field_name), 20, '>')) {
          $error_msg .= CommonMessenger::errorMessageMapping("password_long");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "password_long", $validationErrorMsg);
        }
        break;

      case 'password_confirm':
        if ($form_state->getValue('password') != $form_state->getValue('password_confirm')) {
          $error_msg .= CommonMessenger::errorMessageMapping("password_confirm");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "password_confirm", $validationErrorMsg);
        }
        break;

      case 'gender':
        if ($form_state->getValue($field_name) == NULL) {
          $error_msg .= CommonMessenger::errorMessageMapping("gender");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "gender", $validationErrorMsg);
        }
        break;

      case 'date_of_birth':
        $dob = $form_state->getValue($field_name);
        $date1 = strtr($dob, '/', '-');
        $dob = date('Y-m-d', strtotime($date1));
        $dobCheck = explode("-",$dob);
        // fixing 00/00/0000 issue
        if(in_array( "00" ,$dobCheck ) || in_array( "000" ,$dobCheck ) || in_array( "0000" ,$dobCheck )){
          $error_msg .= CommonMessenger::errorMessageMapping("dob_invalid");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "dob_invalid", $validationErrorMsg);
        }
        $birth = strtotime($dob);
        // For 1970-01-01 year.
        if ($dob == '1970-01-01' || $dob == '01-01-1970') {
          $birth = '00000000';
        }
        // Not getting strtotime.
        if (!$birth) {
          $error_msg .= CommonMessenger::errorMessageMapping("dob_old");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "dob_old", $validationErrorMsg);
        }
        else {
          $year = date("Y", $birth);
          $month = date("m", $birth);
          $day = date("d", $birth);
          $date_field_value = $year . '-' . $month . '-' . $day;
          if (!($this->validateDob($date_field_value))) {
            $error_msg .= CommonMessenger::errorMessageMapping("dob_invalid");
            $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "dob_invalid", $validationErrorMsg);
          }
          // Initializing lp configuration.
          $panelConfiguration = \Drupal::service('lp.configuration');
          // Getting age limit configuration.
          $ageLimit_configuration = $panelConfiguration->getAgeLimitConfiguration();
          if ($ageLimit_configuration['flag']) {
            // Initializing lp configuration.
            $panelConfiguration = \Drupal::service('lp.configuration');
            // Getting age limit configuration.
            $ageLimit_configuration = $panelConfiguration->getAgeLimitConfiguration();

            // Min age as per country.
            $minAgeValue = $ageLimit_configuration['minAgeLimit'];

            // Max age as per country.
            $maxAgeLimit = $ageLimit_configuration['maxAgeLimit'];

            if ($this->isTooYoung($year, $month, $day, $minAgeValue)) {
              $error_msg .= CommonMessenger::errorMessageMapping("dob_young");
              $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "dob_young", $validationErrorMsg);
            }

            if ($this->isTooOld($year, $month, $day, $maxAgeLimit)) {
              $error_msg .= CommonMessenger::errorMessageMapping("dob_old");
              $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "dob_old", $validationErrorMsg);
            }
          }
        }
        break;

      case 'postal_code':
        // Get the client ip.
        // Get the country code from IP.
        $country_Data = MappingUsages::getCountryByClientIP();

        // OP-4910 - To insert space/hyphen in between postal code.
        $countryCode = $country_Data['country_code'];
        $field_value = $this->modificationOnPostalCode($countryCode, $form_state->getValue($field_name));
        if (!($this->validatePostalCode($countryCode, $field_value))) {
          $error_msg .= CommonMessenger::errorMessageMapping("postal_code_" . strtolower($countryCode));
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, "postal_code_" . strtolower($countryCode), $validationErrorMsg);
        }
        break;

      case 'mailing_address1':
      case 'mailing_address2':
        if (!$this->validateMailingAdress($form_state->getValue($field_name)) ||
          $this->validateLength($form_state->getValue($field_name), 1, '<') ||
          $this->validateLength($form_state->getValue($field_name), 80, '>')) {
          $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_regex");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_regex", $validationErrorMsg);
        }
        break;

      case 'state':
      case 'city':
        if (!$this->validateStateCity($form_state->getValue($field_name)) ||
          $this->validateLength($form_state->getValue($field_name), 1, '<') ||
          $this->validateLength($form_state->getValue($field_name), 50, '>')) {
          $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_regex");
          $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_regex", $validationErrorMsg);
        }
        break;

      // OP-3992: LP Registration CN and BR - add open end box for city=other.
      case 'city_other':
        if (!empty($form_state->getValue($field_name))) {
          if (!$this->validateStateCity($form_state->getValue($field_name)) ||
            $this->validateLength($form_state->getValue($field_name), 1, '<') ||
            $this->validateLength($form_state->getValue($field_name), 50, '>')) {
            $error_msg .= CommonMessenger::errorMessageMapping($field_name . "_regex");
            $validationErrorMsg = $this->prepareValidationErr($form_state, $field_name, $field_name . "_regex", $validationErrorMsg);
          }
        }
        break;
    }

    if (strlen($error_msg)) {
      $form_state->setErrorByName($field_name, $error_msg);
    }

    // OP-4251 - validation error coming at Drupal side after submit.
    if (!empty($validationErrorMsg) && count($validationErrorMsg) > 0) {
      return $validationErrorMsg;
    }
  }

  /**
   * Global Function to check the validation without form.
   */
  public function fieldValidateWithoutFrom($field_name, $field_value, $postFields) {
    $error_msg = [];

    // Return if field value is empty.
    if ($field_value == '') {
      return $error_msg;
    }

    switch ($field_name) {
        // Validation for term field.
      case 'terms':
        if ($field_value != 1) {
          $error_msg[$field_name][] = 'term_required';
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name);
        }
        break;

      case 'first_name':
      case 'last_name':

        if (!$this->validateName($field_value)) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        if ($this->validateLength($field_value, 1, '<')) {
          $error_msg[$field_name][] = $field_name . "_short";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_short");
        }
        if ($this->validateLength($field_value, 30, '>')) {
          $error_msg[$field_name][] = $field_name . "_long";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_long");
        }

        break;

      case 'contact_email':
      case 'email_address':
      case 'new_email_address':
        if (!$this->validateEmail($field_value)) {
          $error_msg[$field_name][] = "email_invalid";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("email_invalid");
        }
        if ($this->validateLength($field_value, 50, '>')) {
          $error_msg[$field_name][] = "email_long";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("email_long");
        }
        break;

      case 'confirm_email_address':
        if ($postFields['email_address'] != $postFields['confirm_email_address']) {
          $error_msg[$field_name][] = "email_match";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("email_match");
        }
        break;

      case 'password':
        if (!$this->validatePassword($field_value)) {
          $error_msg[$field_name][] = "password_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("password_regex");
        }
        if ($this->validateLength($field_value, 6, '<')) {
          $error_msg[$field_name][] = "password_short";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("password_short");
        }
        if ($this->validateLength($field_value, 20, '>')) {
          $error_msg[$field_name][] = "password_long";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("password_long");
        }
        break;

      case 'password_confirm':
        if ($postFields['password'] != $postFields['password_confirm']) {
          $error_msg[$field_name][] = "password_confirm";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("password_confirm");
        }
        break;

      case 'gender':
        if ($field_value == NULL) {
          $error_msg[$field_name][] = "gender";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("gender");
        }
        break;

      case 'date_of_birth':
        $dob = $field_value;
        $date1 = strtr($dob, '/', '-');
        $dob = date('Y-m-d', strtotime($date1));
        $dobCheck = explode("-",$dob);
        // fixing 00/00/0000 issue
        if(in_array( "00" ,$dobCheck ) || in_array( "000" ,$dobCheck ) || in_array( "0000" ,$dobCheck )){
          $error_msg[$field_name][] = "dob_invalid";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("dob_invalid");
        }
        $birth = strtotime($dob);
        // For 1970-01-01 year.
        if ($dob == '1970-01-01' || $dob == '01-01-1970') {
          $birth = '00000000';
        }
        // Not getting strtotime.
        if (!$birth) {
          $error_msg[$field_name][] = "dob_old";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("dob_old");
        }
        else {
          $year = date("Y", $birth);
          $month = date("m", $birth);
          $day = date("d", $birth);
          $date_field_value = $year . '-' . $month . '-' . $day;
          if (!($this->validateDob($date_field_value))) {
            $error_msg[$field_name][] = "dob_invalid";
            $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("dob_invalid");
          }
          // Initializing lp configuration.
          $panelConfiguration = \Drupal::service('lp.configuration');
          // Getting age limit configuration.
          $ageLimit_configuration = $panelConfiguration->getAgeLimitConfiguration();
          if ($ageLimit_configuration['flag']) {
            // Initializing lp configuration.
            $panelConfiguration = \Drupal::service('lp.configuration');
            // Getting age limit configuration.
            $ageLimit_configuration = $panelConfiguration->getAgeLimitConfiguration();

            // Min age as per country.
            $minAgeValue = $ageLimit_configuration['minAgeLimit'];

            // Max age as per country.
            $maxAgeLimit = $ageLimit_configuration['maxAgeLimit'];

            if ($this->isTooYoung($year, $month, $day, $minAgeValue)) {
              $options = [];
              $options['age']['min'] = $minAgeValue;
              $error_msg[$field_name][] = "dob_young";
              $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("dob_young");
              $error_msg[$field_name][] = $options;
            }

            if ($this->isTooOld($year, $month, $day, $maxAgeLimit)) {
              $error_msg[$field_name][] = "dob_old";
              $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("dob_old");
            }
          }
        }
        break;

      case 'postal_code':
        // Get the client ip.
        // Get the country code from IP.
        $country_Data = MappingUsages::getCountryByClientIP();

        $countryCode = $country_Data['country_code'];
        // OP-4910 - To insert space/hyphen in between postal code.
        $field_value = $this->modificationOnPostalCode($countryCode, $field_value);
        if (!($this->validatePostalCode($countryCode, $field_value))) {
          $error_msg[$field_name][] = "postal_code_" . strtolower($countryCode);
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping("postal_code_" . strtolower($countryCode));
        }
        break;

      case 'mailing_address1':
      case 'mailing_address2':
        if (!$this->validateMailingAdress($field_value)) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        if ($this->validateLength($field_value, 1, '<')) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        if ($this->validateLength($field_value, 80, '>')) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        break;

      case 'state':
      case 'city':
        if (!$this->validateStateCity($field_value)) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        if ($this->validateLength($field_value, 1, '<')) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        if ($this->validateLength($field_value, 50, '>')) {
          $error_msg[$field_name][] = $field_name . "_regex";
          $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
        }
        break;

        // OP-3992: LP Registration CN and BR - add open end box for city=other.
      case 'city_other':
        if (!empty($field_value)) {
          if (!$this->validateStateCity($field_value)) {
            $error_msg[$field_name][] = $field_name . "_regex";
            $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
          }
          if ($this->validateLength($field_value, 1, '<')) {
            $error_msg[$field_name][] = $field_name . "_regex";
            $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
          }
          if ($this->validateLength($field_value, 50, '>')) {
            $error_msg[$field_name][] = $field_name . "_regex";
            $error_msg[$field_name][] = CommonMessenger::errorMessageMapping($field_name . "_regex");
          }
        }
        break;
    }
    return $error_msg;
  }

  /**
   * Validation function for name.
   * REGEX: Allow alphabetic characters plus spaces and these special characters: . - _  .
   */
  public function validateName($name) {
    // Allowing accented characters.
    $name_regex = "/^[\.\-\_\pL\x{0E00}-\x{0E7F}\s.]+$/u";
    if (preg_match($name_regex, $name)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Validation function for length.
   */
  public function validateLength($field, $length, $op) {
    switch ($op) {
      case '<':
        if (mb_strlen($field) < $length) {
          return TRUE;
        }
        break;

      case '>':
        if (mb_strlen($field) > $length) {
          return TRUE;
        }
        break;

      default:
        // code...
        break;
    }
  }

  /**
   * Validation function for email.
   */
  public static function validateEmail($email) {
    // Removing plus sign from email validation - OP-4379
    $reg = '/^[a-zA-Z0-9][a-zA-Z0-9_.-]*[a-zA-Z0-9_]@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$/';
    if (preg_match($reg, $email)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Validation function for password.
   * FORMAT:  Password must contain one number from 0-9, one lowercase and one uppercase character, one special symbol and have a length between 6 and 20.
   */
  public function validatePassword($password) {
    // Password Regex.
    $password_regex = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,20}$/';
    if (preg_match($password_regex, $password)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Validation function for password.
   * Returns true if the panelist is younger than 16.
   */
  public static function isTooYoung($year, $month, $day, $minAgeValue) {
    $istooyoung = FALSE;
    if ((date("Y") - $year) < $minAgeValue) {
      $istooyoung = TRUE;
    }
    elseif ((date("Y") - $year) == $minAgeValue) {
      if (date("m") < $month) {
        $istooyoung = TRUE;
      }
      elseif (date("m") == $month) {
        if (date("d") < $day) {
          $istooyoung = TRUE;
        }
      }
    }
    return $istooyoung;
  }

  /**
   * Returns true if the panelist is older than 99.
   *
   * @param int $year
   *   Year.
   * @param int $month
   *   Month.
   * @param int $day
   *   Day.
   *
   * @return bool
   **/
  public static function isTooOld($year, $month, $day, $maxAgeLimit) {
    $istooold = FALSE;
    if ((date("Y") - $year) > $maxAgeLimit) {
      $istooold = TRUE;
    }
    elseif ((date("Y") - $year) == $maxAgeLimit) {
      if (date("m") > $month) {
        $istooold = TRUE;
      }
      elseif (date("m") == $month) {
        if (date("d") > $day) {
          $istooold = TRUE;
        }
      }
    }
    return $istooold;
  }

  /**
   * Returns true if the panelist is older than 99.
   *
   * @param string $date
   *   string.
   *
   * @return bool
   **/
  public static function validateDob($dob) {
    $d = \DateTime::createFromFormat('Y-m-d', $dob);
    return $d && $d->format('Y-m-d') == $dob;
  }

  /**
   * Returns true if the Postal code is valid as per country provided.
   *
   * @param string $countryCode
   *   eg: IN, US, UK, AU.
   * @param int $postalCode
   *   of the country.
   *
   * @return bool
   **/
  public static function validatePostalCode($countryCode, $postalCode) {
    /** Regular expression for Postal code
     * IN-> REGEX: 6 digits, first digit between 1-9
     * US-> REGEX: 5 digits, range 00501-99950
     * AU-> REGEX: 4 digits
     * UK/GB-> REGEX: One or two upper or lowercase letters followed by one or two numbers followed by a space followed by one number followed by 2 letters. Length 6-8
     * AR-> REGEX: 1 letter, 4 digits, 3 letters (last 3 letters optional) MINLEN: 5 MAXLEN: 9
     * AT-> REGEX: 4 digits, range 1000-9999
     * BE-> REGEX: 4 digits, range 1000-9999
     * BR-> REGEX: 5 digits, hyphen, 3 digits
     * CA-> REGEX: Letter, number, letter, space, number, letter, number; none of the letters can be D, F, I, O, Q, or U
     * CN-> REGEX: 6 digits
     * CO-> REGEX: 6 digits
     * CZ-> REGEX: 3 digits, space, 2 digits, range 100 00 – 900 00
     * DK-> REGEX: 4 digits, range 1000-9999
     * FI-> REGEX: 5 digits, range 00001-99999
     * FR-> REGEX: 5 digits
     * DE-> REGEX: 5 digits
     * GR-> REGEX: 3 digits, space, 2 digits, range 100 00-899 99
     * HU-> REGEX: 4 digits, range 1000-9999
     * ID-> REGEX: 5 digits, range 10000-99999
     * IE-> REGEX: 3 alphanumeric chars, with the first being a letter from an allowed list of 15, the 2nd and 3rd chars being a number, space, 4 alphanumeric
     * IT-> REGEX: 5 digits,
     * JP-> REGEX: 3 digits, hyphen, 4 digits
     * MY-> REGEX: 5 digits, first 2 digits between 01-98
     * MX-> REGEX: 5 digits, range 01020-98000
     * NL-> REGEX: 4 digits, space, 2 uppercase letters
     * NO-> REGEX: 4 digits, range 0001-9999
     * NZ-> REGEX: 4 digits, range 0100-9899
     * PH-> REGEX: 4 digits
     * PL-> REGEX: 2 digits, hyphen, 3 digits
     * PT-> REGEX: 4 digits, hyphen, 3 digits
     * RO-> REGEX: 6 digits, first 2 digits between 01-92
     * RU-> REGEX: 6 digits, first digit between 1-6
     * SG-> REGEX: 6 digits
     * ZA-> REGEX: 4 digits, range 0001-9999
     * KR-> REGEX: 5 digits
     * ES-> REGEX: 5 digits, first 2 digits between 01-52
     * SE-> REGEX: 5 digits, range 10000-99999
     * CH-> REGEX: 4 digits, range 1000-9999
     * TW-> REGEX: 3 digits or 5 digits
     * TH-> REGEX: 5 digits, range 10000-96999
     * TR-> REGEX: 5 digits
     * VE-> REGEX: 4 digits
     * VN-> REGEX: 6 digits, range 100000-970000
     * CL-> REGEX: 3 or 7 digits, first 3 digits between 100-999, OPTIONAL
     * PE-> REGEX: 5 digits, range 01000-25999, OPTIONAL
    */
    // OP-2840 - Updated regular expression for IE postal code.
    $ZIPREG = [
      "AR" => "^([A-Z]{1}\d{4}\s[A-Z]{3}|[A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4})$",
      "AU" => "^\d{4}$",
      "AT" => "^([1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "BE" => "^([1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "BR" => "^\d{5}\-\d{3}$",
      "CA" => "^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z] [0-9][ABCEGHJ-NPRSTV-Z][0-9]$",
      "CN" => "^\d{6}$",
      "CO" => "^\d{6}$",
      "CZ" => "^[1-9][0-9]{2}\s([0-9]{2})?$",
      "DK" => "^([1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "FI" => "^(0000[1-9]|000[1-8][0-9]|0009[0-9]|00[1-8][0-9]{2}|009[0-8][0-9]|0099[0-9]|0[1-8][0-9]{3}|09[0-8][0-9]{2}|099[0-8][0-9]|0999[0-9]|[1-8][0-9]{4}|9[0-8][0-9]{3}|99[0-8][0-9]{2}|999[0-8][0-9]|9999[0-9])$",
      "FR" => "^\d{5}$",
      "DE" => "^\d{5}$",
      'GR' => "^(([1-7][0-9]{2}|8[0-8][0-9]|89[0-9]) ([0-9][0-9]){1})$",
      "HU" => "^([1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "IN" => "^[1-9][0-9]{5}$",
      "ID" => "^([1-8][0-9]{4}|9[0-8][0-9]{3}|99[0-8][0-9]{2}|999[0-8][0-9]|9999[0-9])$",
      "IE" => "^([aA]|[c-fC-F]|[hH]|[kK]|[nN]|[pP]|[rR]|[tT]|[v-yV-Y])[0-9]{2} [AC-FHKNPRTV-Y0-9]{4}$",
      "IE_3" => "^([aA]|[c-fC-F]|[hH]|[kK]|[nN]|[pP]|[rR]|[tT]|[v-yV-Y])[0-9]{2}$",
      "IT" => "^\d{5}$",
      "JP" => "^\d{3}\-\d{4}$",
      "MY" => "^(0[1-9]|[1-8][0-9]|9[0-8])([0-9]{3})$",
      "MX" => "^(010[2-8][0-9]|0109[0-9]|01[1-9][0-9]{2}|0[2-9][0-9]{3}|[1-8][0-9]{4}|9[0-7][0-9]{3}|98000)$",
      "NL" => "^(\d{4} [A-Z]{2})$",
      "NO" => "^(000[1-9]|00[1-8][0-9]|009[0-9]|0[1-8][0-9]{2}|09[0-8][0-9]|099[0-9]|[1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "NZ" => "^(0[1-8][0-9]{2}|09[0-8][0-9]|099[0-9]|[1-8][0-9]{3}|9[0-7][0-9]{2}|98[0-8][0-9]|989[0-9])$",
      "PH" => "^\d{4}$",
      "PL" => "^\d{2}\-\d{3}$",
      "PT" => "^\d{4}\-\d{3}$",
      "RO" => "^(0[1-9]|[1-8][0-9]|9[0-2])([0-9]{4})$",
      "RU" => "^([1-6])([0-9]{5})$",
      "SG" => "^\d{6}$",
      "ZA" => "^(000[1-9]|00[1-8][0-9]|009[0-9]|0[1-8][0-9]{2}|09[0-8][0-9]|099[0-9]|[1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "KR" => "^\d{5}$",
      "ES" => "^(0[1-9]|[1-4][0-9]|5[0-2])([0-9]{3})$",
      "SE" => "^([1-8][0-9]{4}|9[0-8][0-9]{3}|99[0-8][0-9]{2}|999[0-8][0-9]|9999[0-9])$",
      "CH" => "^([1-8][0-9]{3}|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])$",
      "TW" => "^(?:\d{3}|\d{5})$",
      "TH" => "^([1-8][0-9]{4}|9[0-5][0-9]{3}|96[0-8][0-9]{2}|969[0-8][0-9]|9699[0-9])$",
      "TR" => "^\d{5}$",
      "UK" => "^(\b[a-zA-Z]{1,2}[0-9][a-zA-Z0-9]?\b\s[0-9][A-Za-z]{2})$",
      "US" => "^(0050[1-9]|005[1-9][0-9]|00[6-9][0-9]{2}|0[1-8][0-9]{3}|09[0-8][0-9]{2}|099[0-8][0-9]|0999[0-9]|[1-8][0-9]{4}|9[0-8][0-9]{3}|99[0-8][0-9]{2}|999[0-4][0-9]|99950)$",
      "VE" => "^\d{4}$",
      "VN" => "^([1-8][0-9]{5}|9[0-6][0-9]{4}|970000)$",
      "CL" => "^(?:([1-8][0-9]{2}|9[0-8][0-9]|99[0-9])|([1-8][0-9]{2}|9[0-8][0-9]|99[0-9])([0-9]{4}))$",
      "PE" => "^(0[1-8][0-9]{3}|099[0-8][0-9]|0999[0-9]|1[0-9]{4}|2[0-4][0-9]{3}|25[0-8][0-9]{2}|259[0-8][0-9]|2599[0-9])$",
      "LB" => "^\d{4}$",
    ];
    if ($countryCode == 'GB') {
      $countryCode = 'UK';
    }
    if (isset($ZIPREG[$countryCode]) && $ZIPREG[$countryCode] != '') {

      switch ($countryCode) {

        case 'UK':
          if (preg_match("/" . $ZIPREG[$countryCode] . "/i", $postalCode) === 1) {
            if (strlen($postalCode) < 4 || strlen($postalCode) > 8) {
              return FALSE;
            }
            else {
              return TRUE;
            }
          }

          break;

        case 'NL':
          if (preg_match("/" . $ZIPREG[$countryCode] . "/", $postalCode) === 1) {
            return TRUE;
          }
          else {
            return FALSE;
          }

          break;

        // OP-7293 - LP Registration IE - Select either code.
        // We have two different validation for 3 digit postal code and 7 digit postal code.
        case 'IE':
          $postalCodeLength = strlen($postalCode);
          if ($postalCodeLength == 3) {
            $countryCode = 'IE_3';
          }
          else {
            $countryCode = 'IE';
          }
          if (preg_match("/" . $ZIPREG[$countryCode] . "/i", $postalCode) === 1) {
            return TRUE;
          }
          else {
            return FALSE;
          }
          break;

        default:
          if (preg_match("/" . $ZIPREG[$countryCode] . "/i", $postalCode) === 1) {
            return TRUE;
          }
          else {
            return FALSE;
          }
          break;
      }
    }
    else {
      return FALSE;
    }
  }

  /**
   * Function to validate the email address while DOI email verification.
   */
  public function doiemailverification($field_name) {
    $error_msg = [];
    if (!$this->validateEmail($field_name)) {
      $error_msg[] = 'email_invalid';
    }
    if ($this->validateLength($field_name, 50, '>')) {
      $error_msg[] = 'email_long';
    }
    return $error_msg;
  }

  /**
   * Validation function for Mailing Address.
   * REGEX: Allow alphanumeric characters plus spaces and these special characters: . - , _ # / ° º ª ' ` ( )
   */
  public function validateMailingAdress($address) {
    // Remove space and check the string.
    // Return false if only have spaces.
    if (!strlen(preg_replace('/\s+/', '', $address))) {
      return FALSE;
    }

    // Allowing accented characters.
    // OP-4429 - Allowing special characters . - , _ # / ° º ª ' ` ( )
    $name_regex = "/^[\.\-\,\_\#\/\°\º\ª\`\'\(\)\pL\x{0E00}-\x{0E7F}\pN\s.]+$/u";
    if (preg_match($name_regex, $address)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Validation function for State and City if question type is OPEN.
   * REGEX: Allow alphanumeric characters plus spaces these special characters:  ' ` . - ( ) ª   .
   */
  public function validateStateCity($text) {
    // Remove space and check the string.
    // Return false if only have spaces.
    if (!strlen(preg_replace('/\s+/', '', $text))) {
      return FALSE;
    }
    // Allowing accented characters.
    $name_regex = "/^[\.\-\'\ª\`\(\)\pL\x{0E00}-\x{00E7F}\pN\s.]+$/u";
    if (preg_match($name_regex, $text)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Function to validate join API Parameters.
   */
  public static function validateJoinAPIParam($joinApiParams) {
    // Validate URL.
    if (
      !isset($joinApiParams['offer_id']) || empty($joinApiParams['offer_id']) ||
      !isset($joinApiParams['transaction_id']) || empty($joinApiParams['transaction_id']) ||
      !isset($joinApiParams['lang']) || empty($joinApiParams['lang']) ||
      !isset($joinApiParams['country']) || empty($joinApiParams['country']) ||
      !isset($joinApiParams['contactEmail']) || empty($joinApiParams['contactEmail'])
    ) {
      print CommonMessenger::errorMessageMapping('join_api_missing_param_error');
      exit();
    }

    // Validate email address.
    if (!Validation::validateEmail($joinApiParams['contactEmail'])) {
      print CommonMessenger::errorMessageMapping('email_invalid');
      exit();
    }
    // Validatae Postal Code.
    if (isset($joinApiParams['postalCode'])) {
      // OP-4910 - To insert the space/hyphen in between postalcode.
      $postalCode = Validation::modificationOnPostalCode($joinApiParams['country'], $joinApiParams['postalCode']);
      if (!(Validation::validatePostalCode($joinApiParams['country'], $postalCode))) {
        $error_msg = CommonMessenger::errorMessageMapping('join_api_missing_param_error_new');
        print $error_msg;
        print '<br>';
        print 'Code: 103 Invalid/Missing Parameters';
        exit();
      }
    }
    return TRUE;
  }

  /**
   * Prepare validation error message for error fields.
   */
  protected function prepareValidationErr(&$form_state, $field_name, $message, $validationErrorMsg) {
    if (!array_key_exists($field_name, $validationErrorMsg)) {
      if ($field_name == 'password_confirm' || $field_name == 'password') {
        $validationErrorMsg[$field_name]['value'] = '*****';
      }
      else {
        $validationErrorMsg[$field_name]['value'] = $form_state->getValue($field_name);
      }
    }
    $validationErrorMsg[$field_name]['error'][] = $message;
    return $validationErrorMsg;
  }

  /**
   * Add Drupal logger when validation error occurred after submit (OP-4251).
   *
   * @param [array] $errors
   *   Error information/field value and error messages.
   * @param string $userName
   *   Panelist email id.
   */
  public function validationLogInfo(array $errors, $userName) {
    $logErrors = array_filter($errors);
    $logErrors = array_values($logErrors);
    if (count($logErrors) > 0) {
      $userSession = \Drupal::service('lp.util.session_user');
      $attributes = \Drupal::service('request_stack')->getCurrentRequest()->attributes->all();
      $errorLogMsg = '<pre>';
      if (!empty($userName)) {
        $userName = $userName;
      }
      else {
        $userName = '';
      }
      $errorLogMsg .= "<p>" . $userName . ' - ' . " Error details:</p>";
      $errorLogMsg .= "<p>Controller: " . $attributes['_controller'] . "</p>";
      $errorLogMsg .= "<p>Route: " . $attributes['_route'] . "</p>";
      $errorLogMsg .= "<p>Current Path: " . \Drupal::service('path.current')->getPath() . "</p>";
      $errorLogMsg .= "<p>Referer Path: " . \Drupal::request()->server->get('HTTP_REFERER') . "</p>";
      // Panelist session data.
      if ($userSession->isAuthenticated()) {
        $logSessionData = [];
        $panelistSessionData = $userSession->getPanelistSessionData();
        $logSessionData['panelistId'] = $panelistSessionData['panelistId'];
        $logSessionData['emailAddress'] = $panelistSessionData['emailAddress'];
        $logSessionData['panelistActivePanel'] = $panelistSessionData['panelistActivePanel'];
        $logSessionData['panelistCountryCode'] = $panelistSessionData['panelistCountryCode'];
        $logSessionData['panelistIp'] = $panelistSessionData['panelistIp'];
        $errorLogMsg .= "Panelist Session Data: " . print_r($logSessionData, TRUE);
      }
      $errorLogMsg .= "<p>Data" . print_r($logErrors, TRUE) . "</p>";
      $errorLogMsg .= '</pre>';
      \Drupal::logger('lp_validation')->error($errorLogMsg);
    }
  }

  /**
   * Function to insert the space and hypen in between postalcode.
   */
  public static function insertSpaceOrHypen($postalCode, $spaceOrHypen, $index) {
    $count = substr_count($postalCode, $spaceOrHypen);
    // To check the spaces/hyphen count
    if ($count == 0) {
      $insertion = $spaceOrHypen;
      $postalCode = substr_replace($postalCode, $insertion, $index, 0);
    }
    return $postalCode;
  }

  /**
   * Function to modify postal code for required country.
   */
  public static function modificationOnPostalCode($countryCode, $postalCode) {
    if ($countryCode == 'GB') {
      $countryCode = 'UK';
    }
    if (isset($countryCode) && $countryCode != '') {

      switch ($countryCode) {

        case 'UK':
          // OP-5802 - Store postal code letters as uppercase
          $postalCode = strtoupper($postalCode);
          if (strlen($postalCode) > 6) {
            $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 4);
            return $postalCode;
          }
          else if (strlen($postalCode) > 5) {
            $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 3);
            return $postalCode;
          }
          else {
            $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 2);
            return $postalCode;
          }
        break;

        case 'CA':
        case 'GR':
        case 'CZ':
          // OP-5802 - Store postal code letters as uppercase
          $postalCode = strtoupper($postalCode);
          $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 3);
          return $postalCode;
        break;

        case 'IE':
          // OP-7293 - LP Registration IE - Select either code.
          // Add space if entered postal code is greater than 3 digits.
          $postalCodeLength = strlen($postalCode);
          if ($postalCodeLength > 3) {
            $postalCode = strtoupper($postalCode);
            $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 3);
          }
          return $postalCode;
        break;

        case 'NL':
          // OP-5802 - Store postal code letters as uppercase
          $postalCode = strtoupper($postalCode);
          $postalCode = Validation::insertSpaceOrHypen($postalCode, " ", 4);
          return $postalCode;
        break;

        case 'AR':
          // OP-5802 - Store postal code letters as uppercase
          $postalCode = strtoupper($postalCode);
          // OP-5606 - Allow panelist with/Without space but store postal code without space.
          // String length > 5 and space count == 1, Remove the space from string.
          if (strlen($postalCode) > 5 && substr_count($postalCode, " ") == 1) {
            $postalCode = preg_replace('/\s+/', '', $postalCode);
            return $postalCode;
          }
          else {
            return $postalCode;
          }
        break;

        case 'BR':
          $postalCode = Validation::insertSpaceOrHypen($postalCode, "-", 5);
          return $postalCode;
        break;

        case 'PL':
          $postalCode = Validation::insertSpaceOrHypen($postalCode, "-", 2);
          return $postalCode;
        break;

        case 'PT':
          $postalCode = Validation::insertSpaceOrHypen($postalCode, "-", 4);
          return $postalCode;
        break;

        case 'JP':
          $postalCode = Validation::insertSpaceOrHypen($postalCode, "-", 3);
          return $postalCode;
        break;

        default:
          return $postalCode;
          break;
      }
    }
    else {
      return $postalCode;
    }
  }

}
