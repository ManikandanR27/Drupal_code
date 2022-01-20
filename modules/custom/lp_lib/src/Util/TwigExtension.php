<?php

namespace Drupal\lp_lib\Util;

/**
 * Twig exension for replace token on member session.
 */
class TwigExtension extends \Twig_Extension {

  protected $userSession;

  /**
   * Construct for intialise object.
   */
  public function __construct($sessionData) {
    $this->userSession = $sessionData;
  }

  /**
   * Function to get name for token from lifepoints sass.
   */
  public function getName() {
    return 'lifepoints_sass_token_replacement';
  }

  /**
   * In this for declare the extension function.
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('token_replacement',
      [$this, 'token_replacement'],
      ['is_safe' => ['html']]),
    ];
  }

  /**
   * Token Replacing fields with memeber session .
   */
  public function token_replacement($text,$panelistSessionData = []) {
    // Assign $newText by reference to the actual string value.
    if (is_string($text)) {
      // Straight string.
      $newText = &$text;
    }
    else if (is_array($text)) {
      // Render array.
      if (isset($text['#text'])) {
        $newText = &$text['#text'];
      }
      else if (isset($text['#markup'])) {
        $newText = &$text['#markup'];
      }
    }
    // OP-6450 - Checking if input data availabel 
    if (isset($panelistSessionData) && !empty($panelistSessionData)){
      $panelistData = $panelistSessionData; 
    }
    else {
      $panelistData = $this->userSession->getPanelistSessionData();
    }
    
    if (empty($panelistData)) {
      $newText = str_replace('@firstName', "", $newText);
    }

    if (!empty($text) && isset($panelistData) && !empty($panelistData)) {
      foreach ($panelistData as $key => $value) {
        if ($key != '') {
          $search = '@' . $key;

          if ($key == 'createDate') {

            if ($value != '') {
              $year = date("Y", $value / 1000);
              $month = date("m", $value / 1000);
              $day = date("d", $value / 1000);
              $yearPrefix = t("Influencing brands across the globe since ");
              $newText = str_replace('@createdYear', $yearPrefix . $year, $newText);
            }
            else {
              $newText = str_replace('@createdYear', "", $newText);
            }

          }
          else {
            if (!is_array($value) && !is_object($value)) {
              $newText = str_replace($search, $value, $newText);
            }
          }

        }
      }
      if (strpos($newText, '@year') !== FALSE) {
        if ($panelistData['createDate'] != '') {
          $newText = str_replace('@year', date("Y", $panelistData['createDate'] / 1000), $newText);
        }
        else {
          $newText = str_replace('@year', "", $newText);
        }

      }

    }

    // Return the original variable, that has its string value changed by-reference with $newText.
    return $text;
  }

}
