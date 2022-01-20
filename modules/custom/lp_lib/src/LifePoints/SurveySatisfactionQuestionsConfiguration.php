<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\field\Entity\FieldStorageConfig;
/**
 * Survey Satisfaction Questions configuration class.
 */
class SurveySatisfactionQuestionsConfiguration {

  /**
   * SHS Panel Title.
   */
  protected $shsPanelTitle;
  /**
   * SHS Form Page Title.
   */
  protected $shsPageTitle;
  /**
   * SHS Question Enjoyable.
   */
  protected $shsQuestionEnjoyable;
  /**
   * SHS Response Enjoyable.
   */
  protected $shsResponseEnjoyable;
  /**
   * SHS Question Easy.
   */
  protected $shsQuestionEasy;
  /**
   * SHS Response Easy.
   */
  protected $shsResponseEasy;
  /**
   * SHS Question Wantmore.
   */
  protected $shsQuestionWantmore;
  /**
   * SHS Response Wantmore.
   */
  protected $shsResponseWantmore;
  /**
   * SHS Question Reason.
   */
  protected $shsQuestionReason;
  /**
   * SSQ Form Button Label.
   */
  protected $shsContinueButtonLabel;
  /**
   * SHS Version Id.
   */
  protected $shsVersionId;

  /**
   * Construct to intialise object.
   */
  public function __construct() {
    $this->setSurveySatisfactionQuestionsConfiguration();
  }

  /**
   * Function to set survey satisfaction question configuration.
   */
  public function setSurveySatisfactionQuestionsConfiguration() {
    $properties = [];
    // Panel name.
    $properties['name'] = 'Default';
    $properties['vid'] = 'survey_satisfaction_questions';
    // Get the survey settings data based on the property defined.
    $this->surveySatisfactionQuestionsData = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->surveySatisfactionQuestionsData = reset($this->surveySatisfactionQuestionsData);

    // Get Drupal Language set.
    $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($this->surveySatisfactionQuestionsData->hasTranslation($drupal_language)) {
      $this->surveySatisfactionQuestionsData = $this->surveySatisfactionQuestionsData->getTranslation($drupal_language);
    }

    $this->shsPanelTitle = $this->surveySatisfactionQuestionsData->getName();
    if ($this->surveySatisfactionQuestionsData) {
      // SHS Page Title.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_page_title) && count($this->surveySatisfactionQuestionsData->field_shs_page_title->getValue())) {
        $this->shsPageTitle = $this->surveySatisfactionQuestionsData->field_shs_page_title->getValue()[0]['value'];
      }
      // SHS Question Enjoyable.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_question_enjoyable) && count($this->surveySatisfactionQuestionsData->field_shs_question_enjoyable->getValue())) {
        $this->shsQuestionEnjoyable = $this->surveySatisfactionQuestionsData->field_shs_question_enjoyable->getValue()[0]['value'];
      }
      // SHS Response Enjoyable.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_response_enjoyable) && count($this->surveySatisfactionQuestionsData->field_shs_response_enjoyable->getValue())) {
        // Get field strorage for the language based translations.
        $field_shs_response_enjoyable = FieldStorageConfig::loadByName('taxonomy_term', 'field_shs_response_enjoyable');
        $shsResponseEnjoyableTranslatedOptions = $field_shs_response_enjoyable->getSettings()['allowed_values'];
        $shsResponseEnjoyableOptionsAllowedValue = [];
        foreach ($this->surveySatisfactionQuestionsData->field_shs_response_enjoyable->getValue() as $options) {
          $shsResponseEnjoyableOptionsAllowedValue[$options['value']] = $shsResponseEnjoyableTranslatedOptions[$options['value']];
        }
        $this->shsResponseEnjoyable = $shsResponseEnjoyableOptionsAllowedValue;
      }
      // SHS Question Easy.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_question_easy) && count($this->surveySatisfactionQuestionsData->field_shs_question_easy->getValue())) {
        $this->shsQuestionEasy = $this->surveySatisfactionQuestionsData->field_shs_question_easy->getValue()[0]['value'];
      }
      // SHS Response Easy.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_response_easy) && count($this->surveySatisfactionQuestionsData->field_shs_response_easy->getValue())) {
        // Get field strorage for the language based translations.
        $field_shs_response_easy = FieldStorageConfig::loadByName('taxonomy_term', 'field_shs_response_easy');
        $shsResponseEasyTranslatedOptions = $field_shs_response_easy->getSettings()['allowed_values'];
        $shsResponseEasyOptionsAllowedValue = [];
        foreach ($this->surveySatisfactionQuestionsData->field_shs_response_easy->getValue() as $options) {
          $shsResponseEasyOptionsAllowedValue[$options['value']] = $shsResponseEasyTranslatedOptions[$options['value']];
        }
        $this->shsResponseEasy = $shsResponseEasyOptionsAllowedValue;
      }
      // SHS Question Wantmore.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_question_wantmore) && count($this->surveySatisfactionQuestionsData->field_shs_question_wantmore->getValue())) {
        $this->shsQuestionWantmore = $this->surveySatisfactionQuestionsData->field_shs_question_wantmore->getValue()[0]['value'];
      }
      // SHS Response Wantmore.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_response_wantmore) && count($this->surveySatisfactionQuestionsData->field_shs_response_wantmore->getValue())) {
        $shsResponseWantmoreOptions = $this->surveySatisfactionQuestionsData->field_shs_response_wantmore->getSetting('allowed_values');
        $shsResponseWantmoreOptionsAllowedValue = [];
        foreach ($this->surveySatisfactionQuestionsData->field_shs_response_wantmore->getValue() as $options) {
          $shsResponseWantmoreOptionsAllowedValue[$options['value']] = $shsResponseWantmoreOptions[$options['value']];
        }
        $this->shsResponseWantmore = $shsResponseWantmoreOptionsAllowedValue;
      }
      // SHS Question Reason.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_question_reason) && count($this->surveySatisfactionQuestionsData->field_shs_question_reason->getValue())) {
        $this->shsQuestionReason = $this->surveySatisfactionQuestionsData->field_shs_question_reason->getValue()[0]['value'];
      }
      // SHS Continue Button Label.
      if (isset($this->surveySatisfactionQuestionsData->field_button_label) && count($this->surveySatisfactionQuestionsData->field_button_label->getValue())) {
        $this->shsContinueButtonLabel = $this->surveySatisfactionQuestionsData->field_button_label->getValue()[0]['value'];
      }
      // SHS Version ID.
      if (isset($this->surveySatisfactionQuestionsData->field_shs_version_id) && count($this->surveySatisfactionQuestionsData->field_shs_version_id->getValue())) {
        $this->shsVersionId = $this->surveySatisfactionQuestionsData->field_shs_version_id->getValue()[0]['value'];
      }
    }
  }

  /**
   * Get function for fetching all survey satisfaction question configurations.
   */
  public function getSurveySatisfactionQuestionsConfiguration() {
    return [
      'shsPanelTitle' => $this->shsPanelTitle,
      'shsPageTitle' => $this->shsPageTitle,
      'shsQuestionEnjoyable' => $this->shsQuestionEnjoyable,
      'shsResponseEnjoyable' => $this->shsResponseEnjoyable,
      'shsQuestionEasy' => $this->shsQuestionEasy,
      'shsResponseEasy' => $this->shsResponseEasy,
      'shsQuestionWantmore' => $this->shsQuestionWantmore,
      'shsResponseWantmore' => $this->shsResponseWantmore,
      'shsQuestionReason' => $this->shsQuestionReason,
      'shsContinueButtonLabel' => $this->shsContinueButtonLabel,
      'shsVersionId' => $this->shsVersionId,
    ];
  }

}
