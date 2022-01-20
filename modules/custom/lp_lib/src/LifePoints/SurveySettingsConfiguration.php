<?php

namespace Drupal\lp_lib\LifePoints;

/**
 * Survey settings configuration class.
 */
class SurveySettingsConfiguration {

  protected $featuredSurveyLevel1Limit;
  protected $featuredSurveyLevel2Limit;
  protected $featuredSurveyLevel3Limit;
  protected $featuredSurveyLevel4Limit;
  protected $moreSurveyLevel1Limit;
  protected $moreSurveyLevel2Limit;
  protected $moreSurveyLevel3Limit;
  protected $moreSurveyLevel4Limit;
  protected $moreSurveyLevel5Limit;
  protected $onGoingSurveysMaxLimit;
  protected $surveyTitlesDisplayText;
  protected $surveyTitleCharacterLimit;
  /**
   * OP-6212 - Newbie Surveys.
   */
  protected $newbieTopSurveysMaxLimit;

  /**
   * Construct to intialise object.
   */
  public function __construct() {
    $this->setSurveyConfiguration();
  }

  /**
   * Function to set panel data configuration.
   */
  public function setSurveyConfiguration() {
    $properties = [];
    // Panel name.
    $properties['name'] = 'Default';
    $properties['vid'] = 'survey_settings';
    // Get the survey settings data based on the property defined.
    $this->surveySettingsData = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->surveySettingsData = reset($this->surveySettingsData);

    if ($this->surveySettingsData) {
      if (isset($this->surveySettingsData->field_featured_survey_l1_limit) && count($this->surveySettingsData->field_featured_survey_l1_limit->getValue())) {
        $this->featuredSurveyLevel1Limit = $this->surveySettingsData->field_featured_survey_l1_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_featured_survey_l2_limit) && count($this->surveySettingsData->field_featured_survey_l2_limit->getValue())) {
        $this->featuredSurveyLevel2Limit = $this->surveySettingsData->field_featured_survey_l2_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_featured_survey_l3_limit) && count($this->surveySettingsData->field_featured_survey_l3_limit->getValue())) {
        $this->featuredSurveyLevel3Limit = $this->surveySettingsData->field_featured_survey_l3_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_featured_survey_l4_limit) && count($this->surveySettingsData->field_featured_survey_l4_limit->getValue())) {
        $this->featuredSurveyLevel4Limit = $this->surveySettingsData->field_featured_survey_l4_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_more_survey_l1_limit) && count($this->surveySettingsData->field_more_survey_l1_limit->getValue())) {
        $this->moreSurveyLevel1Limit = $this->surveySettingsData->field_more_survey_l1_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_more_survey_l2_limit) && count($this->surveySettingsData->field_more_survey_l2_limit->getValue())) {
        $this->moreSurveyLevel2Limit = $this->surveySettingsData->field_more_survey_l2_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_more_survey_l3_limit) && count($this->surveySettingsData->field_more_survey_l3_limit->getValue())) {
        $this->moreSurveyLevel3Limit = $this->surveySettingsData->field_more_survey_l3_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_more_survey_l4_limit) && count($this->surveySettingsData->field_more_survey_l4_limit->getValue())) {
        $this->moreSurveyLevel4Limit = $this->surveySettingsData->field_more_survey_l4_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_more_survey_l5_limit) && count($this->surveySettingsData->field_more_survey_l5_limit->getValue())) {
        $this->moreSurveyLevel5Limit = $this->surveySettingsData->field_more_survey_l5_limit->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_on_going_surveys_max_limit) && count($this->surveySettingsData->field_on_going_surveys_max_limit->getValue())) {
        $this->onGoingSurveysMaxLimit = $this->surveySettingsData->field_on_going_surveys_max_limit->getValue()[0]['value'];
      }
      // OP-6212 - Newbie Surveys.
      if (isset($this->surveySettingsData->field_newbie_top_surveys_limit) && $this->surveySettingsData->field_newbie_top_surveys_limit->count()) {
        $this->newbieTopSurveysMaxLimit = $this->surveySettingsData->field_newbie_top_surveys_limit->getValue()[0]['value'];
      }
      // OP-4395 LP Portal: Assign Default Survey Titles
      // Added field to store survey default title based on display_text.
      if (isset($this->surveySettingsData->field_survey_titles) && count($this->surveySettingsData->field_survey_titles->getValue())) {
        $drupal_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        // If translation available then used translated term.
        if ($this->surveySettingsData->hasTranslation($drupal_language)) {
          $surveySettingsTranslated = \Drupal::service('entity.repository')->getTranslationFromContext($this->surveySettingsData, $drupal_language);
          $this->surveyTitlesDisplayText = $surveySettingsTranslated->field_survey_titles->getValue()[0]['value'];
        }
        else {
          // If translation not available then used default english content.
          $this->surveyTitlesDisplayText = $this->surveySettingsData->field_survey_titles->getValue()[0]['value'];
        }
      }
      // OP-4677: Added field to configure character limit for survey titles.
      if (isset($this->surveySettingsData->field_survey_title_character_lim) && count($this->surveySettingsData->field_survey_title_character_lim->getValue())) {
        $this->surveyTitleCharacterLimit = $this->surveySettingsData->field_survey_title_character_lim->getValue()[0]['value'];
      }
    }
  }

  /**
   * Getter for all survey configurations.
   */
  public function getSurveysConfiguration() {
    return [
      'featuredSurveyLevel1Limit' => $this->featuredSurveyLevel1Limit,
      'featuredSurveyLevel2Limit' => $this->featuredSurveyLevel2Limit,
      'featuredSurveyLevel3Limit' => $this->featuredSurveyLevel3Limit,
      'featuredSurveyLevel4Limit' => $this->featuredSurveyLevel4Limit,
      'moreSurveyLevel1Limit' => $this->moreSurveyLevel1Limit,
      'moreSurveyLevel2Limit' => $this->moreSurveyLevel2Limit,
      'moreSurveyLevel3Limit' => $this->moreSurveyLevel3Limit,
      'moreSurveyLevel4Limit' => $this->moreSurveyLevel4Limit,
      'moreSurveyLevel5Limit' => $this->moreSurveyLevel5Limit,
      'onGoingSurveysMaxLimit' => $this->onGoingSurveysMaxLimit,
      'surveyTitlesDisplayText' => $this->surveyTitlesDisplayText,
      'surveyTitleCharacterLimit' => $this->surveyTitleCharacterLimit,
      'newbieTopSurveysMaxLimit' => isset($this->newbieTopSurveysMaxLimit) ? $this->newbieTopSurveysMaxLimit : 0,
    ];
  }

}
