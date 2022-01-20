<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\Util\MappingUsages;

/**
 * Survey settings configuration class.
 */
class SurveyEndMessagesConfiguration {
  /**
   * Default Title (default else case).
   */
  protected $field_title_default_else;
  /**
   * Description 1 for Respondent Status BadGeoip OR RelevantIdBadGeoip.
   */
  protected $field_desc1_badgeoip;
  /**
   * Description 1 for Respondent Status BadProjectToken and points earned is empty and points earned <= 0.
   */
  protected $field_desc1_badtoken_0;
  /**
   * Description 1 for Respondent Status BadProjectToken and points earned is not empty and points earned > 0.
   */
  protected $field_desc1_badtoken_grt_0;
  /**
   * Description 1 for Respondent Status EarlyScreenout and points earned is empty and points earned <= 0.
   */
  protected $field_desc1_earlyscrout_0;
  /**
   * Description 1 for Respondent Status EarlyScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_desc1_earlyscrout_grt_0;
  /**
   * Description 1 for Respondent Status Fraudulent.
   */
  protected $field_desc1_fraud;
  /**
   * Description 1 for Respondent Status PossibleDuplicate OR RelevantIdDuplicate.
   */
  protected $field_desc1_duplicate;
  /**
   * Description 1 for Respondent Status PreScreenTerminate.
   */
  protected $field_desc1_prescr_term;
  /**
   * Description 1 for Respondent Status QualityTerminate and points earned is not empty and points earned > 0.
   */
  protected $field_desc1_qualitytermi_grt_0;
  /**
   * Description 1 for Respondent Status QuotaFull.
   */
  protected $field_desc1_quotafull;
  /**
   * Description 1 for Respondent Status RelevantIdFailure.
   */
  protected $field_desc1_relvidfail;
  /**
   * Description 1 for Respondent Status RelevantIdFraudulent.
   */
  protected $field_desc1_relvidfraud;
  /**
   * Description 1 for Respondent Status SurveyClosed.
   */
  protected $field_desc1_surveyclosed;
  /**
   * Description 1 for Respondent Status TargetableScreenout and Points Earned is empty OR Points Earned < 0.
   */
  protected $field_desc1_target_scrout_0;
  /**
   * Description 1 for Respondent Status TargetableScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_desc1_target_scrout_grt_0;
  /**
   * Description 1 for Respondent Status TechnicalTerminate.
   */
  protected $field_desc1_tech_term;
  /**
   * Description 1 for Survey Status CompletedPoints OR Respondent Status Complete and Point Earned not empty and Points Earned > 0.
   */
  protected $field_desc1_complete_grt_0;
  /**
   * Description 2 for Respondent Status BadProjectToken and points earned is empty and points earned <= 0.
   */
  protected $field_desc2_badtoken_0;
  /**
   * Description 2 for Respondent Status BadProjectToken and points earned is not empty and points earned > 0.
   */
  protected $field_desc2_badtoken_grt_0;
  /**
   * Description 2 for Respondent Status EarlyScreenout and points earned is empty and points earned <= 0.
   */
  protected $field_desc2_earlyscrout_0;
  /**
   * Description 2 for Respondent Status EarlyScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_desc2_earlyscrout_grt_0;
  /**
   * Description 2 for Respondent Status Fraudulent.
   */
  protected $field_desc2_fraud;
  /**
   * Description 2 for Respondent Status PossibleDuplicate OR RelevantIdDuplicate.
   */
  protected $field_desc2_duplicate;
  /**
   * Description 2 for Respondent Status PreScreenTerminate.
   */
  protected $field_desc2_prescr_term;
  /**
   * Description 2 for Respondent Status QuotaFull.
   */
  protected $field_desc2_quotafull;
  /**
   * Description 2 for Respondent Status RelevantIdFraudulent.
   */
  protected $field_desc2_relvidfraud;
  /**
   * Description 2 for Respondent Status SurveyClosed.
   */
  protected $field_desc2_surveyclosed;
  /**
   * Description 2 for Respondent Status TargetableScreenout and Points Earned is empty OR Points Earned < 0.
   */
  protected $field_desc2_target_scrout_0;
  /**
   * Description 2 for Respondent Status TargetableScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_desc2_target_scrout_grt_0;
  /**
   * Description 2 for Respondent Status TechnicalTerminate.
   */
  protected $field_desc2_tech_term;
  /**
   * Description 3 for Respondent Status PossibleDuplicate and Points Earned is empty OR Points Earned < 0.
   */
  protected $field_desc3_duplicate_0;
  /**
   * Description 3 for Respondent Status TechnicalTerminate.
   */
  protected $field_desc3_tech_term;
  /**
   * Description 4 for Respondent Status PossibleDuplicate and Points Earned is empty OR Points Earned < 0.
   */
  protected $field_desc4_duplicate_0;
  /**
   * Link text for Points earned is not empty and points earned > 0 (default else case).
   */
  protected $field_link_default_else;
  /**
   * Link text for Respondent Status QualityTerminate.
   */
  protected $field_link_qualitytermi_grt_0;
  /**
   * Link text for Respondent Status Suspicious.
   */
  protected $field_link_suspicious;
  /**
   * Link text for survey status equals to CompletedPoints OR respondent status equals to Complete.
   */
  protected $field_link_complete;
  /**
   * Title for Respondent Status BadGeoip OR RelevantIdBadGeoip.
   */
  protected $field_title_badgeoip;
  /**
   * Title for Respondent Status BadProjectToken and points earned is empty and points earned <= 0.
   */
  protected $field_title_badtoken_0;
  /**
   * Title for Respondent Status BadProjectToken and points earned is not empty and points earned > 0.
   */
  protected $field_title_badtoken_grt_0;
  /**
   * Title for Respondent Status EarlyScreenout and points earned is empty and points earned <= 0.
   */
  protected $field_title_earlyscrout_0;
  /**
   * Title for Respondent Status EarlyScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_title_earlyscrout_grt_0;
  /**
   * Title for Respondent Status Fraudulent.
   */
  protected $field_title_fraud;
  /**
   * Title for Respondent Status PossibleDuplicate OR RelevantIdDuplicate.
   */
  protected $field_title_duplicate;
  /**
   * Title for Respondent Status PreScreenTerminate.
   */
  protected $field_title_prescr_term;
  /**
   * Title for Respondent Status QualityTerminate.
   */
  protected $field_title_qualityterminate;
  /**
   * Title for Respondent Status QuotaFull.
   */
  protected $field_title_quotafull;
  /**
   * Title for Respondent Status RelevantIdFailure.
   */
  protected $field_title_relvidfail;
  /**
   * Title for Respondent Status RelevantIdFraudulent.
   */
  protected $field_title_relvidfraud;
  /**
   * Title for Respondent Status SurveyClosed.
   */
  protected $field_title_surveyclosed;
  /**
   * Title for Respondent Status Suspicious.
   */
  protected $field_title_suspicious;
  /**
   * Title for Respondent Status TargetableScreenout and points earned is empty or points earned < 0.
   */
  protected $field_title_target_scrout_0;
  /**
   * Title for Respondent Status TargetableScreenout and points earned is not empty and points earned > 0.
   */
  protected $field_title_target_scrout_grt_0;
  /**
   * Title for Respondent Status TechnicalTerminate.
   */
  protected $field_title_tech_term;
  /**
   * Title for Survey status CompletedPoints OR Respondent Status Complete and Points Earned is empty or Points Earned <= 0.
   */
  protected $field_title_complete_0;
  /**
   * Title for Survey status CompletedPoints OR Respondent Status Complete and Points Earned is not empty and Points Earned > 0.
   */
  protected $field_title_complete_grt_0;

  /**
   * Construct to intialise object.
   */
  public function __construct() {
    $panel_code = MappingUsages::get_panel_code();
    if (!empty($panel_code)) {
      $this->setSurveyEndMessageConfiguration($panel_code);
    }
  }

  /**
   * Function to set panel data configuration.
   */
  public function setSurveyEndMessageConfiguration($panel) {
    $properties = [];
    // Panel name.
    $properties['name'] = $panel;
    $properties['vid'] = 'survey_end_messages';
    // Get the survey settings data based on the property defined.
    $this->surveySettingsData = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->surveySettingsData = reset($this->surveySettingsData);

    if ($this->surveySettingsData) {
      if (isset($this->surveySettingsData->field_title_default_else) && count($this->surveySettingsData->field_title_default_else->getValue())) {
        $this->field_title_default_else = $this->surveySettingsData->field_title_default_else->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_badgeoip) && count($this->surveySettingsData->field_desc1_badgeoip->getValue())) {
        $this->field_desc1_badgeoip = $this->surveySettingsData->field_desc1_badgeoip->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_badtoken_0) && count($this->surveySettingsData->field_desc1_badtoken_0->getValue())) {
        $this->field_desc1_badtoken_0 = $this->surveySettingsData->field_desc1_badtoken_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_badtoken_grt_0) && count($this->surveySettingsData->field_desc1_badtoken_grt_0->getValue())) {
        $this->field_desc1_badtoken_grt_0 = $this->surveySettingsData->field_desc1_badtoken_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_earlyscrout_0) && count($this->surveySettingsData->field_desc1_earlyscrout_0->getValue())) {
        $this->field_desc1_earlyscrout_0 = $this->surveySettingsData->field_desc1_earlyscrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_earlyscrout_grt_0) && count($this->surveySettingsData->field_desc1_earlyscrout_grt_0->getValue())) {
        $this->field_desc1_earlyscrout_grt_0 = $this->surveySettingsData->field_desc1_earlyscrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_fraud) && count($this->surveySettingsData->field_desc1_fraud->getValue())) {
        $this->field_desc1_fraud = $this->surveySettingsData->field_desc1_fraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_duplicate) && count($this->surveySettingsData->field_desc1_duplicate->getValue())) {
        $this->field_desc1_duplicate = $this->surveySettingsData->field_desc1_duplicate->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_prescr_term) && count($this->surveySettingsData->field_desc1_prescr_term->getValue())) {
        $this->field_desc1_prescr_term = $this->surveySettingsData->field_desc1_prescr_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_qualitytermi_grt_0) && count($this->surveySettingsData->field_desc1_qualitytermi_grt_0->getValue())) {
        $this->field_desc1_qualitytermi_grt_0 = $this->surveySettingsData->field_desc1_qualitytermi_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_quotafull) && count($this->surveySettingsData->field_desc1_quotafull->getValue())) {
        $this->field_desc1_quotafull = $this->surveySettingsData->field_desc1_quotafull->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_relvidfail) && count($this->surveySettingsData->field_desc1_relvidfail->getValue())) {
        $this->field_desc1_relvidfail = $this->surveySettingsData->field_desc1_relvidfail->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_relvidfraud) && count($this->surveySettingsData->field_desc1_relvidfraud->getValue())) {
        $this->field_desc1_relvidfraud = $this->surveySettingsData->field_desc1_relvidfraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_surveyclosed) && count($this->surveySettingsData->field_desc1_surveyclosed->getValue())) {
        $this->field_desc1_surveyclosed = $this->surveySettingsData->field_desc1_surveyclosed->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_target_scrout_0) && count($this->surveySettingsData->field_desc1_target_scrout_0->getValue())) {
        $this->field_desc1_target_scrout_0 = $this->surveySettingsData->field_desc1_target_scrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_target_scrout_grt_0) && count($this->surveySettingsData->field_desc1_target_scrout_grt_0->getValue())) {
        $this->field_desc1_target_scrout_grt_0 = $this->surveySettingsData->field_desc1_target_scrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_tech_term) && count($this->surveySettingsData->field_desc1_tech_term->getValue())) {
        $this->field_desc1_tech_term = $this->surveySettingsData->field_desc1_tech_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc1_complete_grt_0) && count($this->surveySettingsData->field_desc1_complete_grt_0->getValue())) {
        $this->field_desc1_complete_grt_0 = $this->surveySettingsData->field_desc1_complete_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_complete_grt_0) && count($this->surveySettingsData->field_desc2_complete_grt_0->getValue())) {
        $this->field_desc2_complete_grt_0 = $this->surveySettingsData->field_desc2_complete_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_badtoken_0) && count($this->surveySettingsData->field_desc2_badtoken_0->getValue())) {
        $this->field_desc2_badtoken_0 = $this->surveySettingsData->field_desc2_badtoken_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_badtoken_grt_0) && count($this->surveySettingsData->field_desc2_badtoken_grt_0->getValue())) {
        $this->field_desc2_badtoken_grt_0 = $this->surveySettingsData->field_desc2_badtoken_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_earlyscrout_0) && count($this->surveySettingsData->field_desc2_earlyscrout_0->getValue())) {
        $this->field_desc2_earlyscrout_0 = $this->surveySettingsData->field_desc2_earlyscrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_earlyscrout_grt_0) && count($this->surveySettingsData->field_desc2_earlyscrout_grt_0->getValue())) {
        $this->field_desc2_earlyscrout_grt_0 = $this->surveySettingsData->field_desc2_earlyscrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_fraud) && count($this->surveySettingsData->field_desc2_fraud->getValue())) {
        $this->field_desc2_fraud = $this->surveySettingsData->field_desc2_fraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_duplicate) && count($this->surveySettingsData->field_desc2_duplicate->getValue())) {
        $this->field_desc2_duplicate = $this->surveySettingsData->field_desc2_duplicate->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_target_scrout_0) && count($this->surveySettingsData->field_desc2_target_scrout_0->getValue())) {
        $this->field_desc2_target_scrout_0 = $this->surveySettingsData->field_desc2_target_scrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_prescr_term) && count($this->surveySettingsData->field_desc2_prescr_term->getValue())) {
        $this->field_desc2_prescr_term = $this->surveySettingsData->field_desc2_prescr_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_quotafull) && count($this->surveySettingsData->field_desc2_quotafull->getValue())) {
        $this->field_desc2_quotafull = $this->surveySettingsData->field_desc2_quotafull->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_relvidfraud) && count($this->surveySettingsData->field_desc2_relvidfraud->getValue())) {
        $this->field_desc2_relvidfraud = $this->surveySettingsData->field_desc2_relvidfraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_surveyclosed) && count($this->surveySettingsData->field_desc2_surveyclosed->getValue())) {
        $this->field_desc2_surveyclosed = $this->surveySettingsData->field_desc2_surveyclosed->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_target_scrout_grt_0) && count($this->surveySettingsData->field_desc2_target_scrout_grt_0->getValue())) {
        $this->field_desc2_target_scrout_grt_0 = $this->surveySettingsData->field_desc2_target_scrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc2_tech_term) && count($this->surveySettingsData->field_desc2_tech_term->getValue())) {
        $this->field_desc2_tech_term = $this->surveySettingsData->field_desc2_tech_term->getValue()[0]['value'];
      }

      if (isset($this->surveySettingsData->field_desc3_duplicate_0) && count($this->surveySettingsData->field_desc3_duplicate_0->getValue())) {
        $this->field_desc3_duplicate_0 = $this->surveySettingsData->field_desc3_duplicate_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc3_tech_term) && count($this->surveySettingsData->field_desc3_tech_term->getValue())) {
        $this->field_desc3_tech_term = $this->surveySettingsData->field_desc3_tech_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_desc4_duplicate_0) && count($this->surveySettingsData->field_desc4_duplicate_0->getValue())) {
        $this->field_desc4_duplicate_0 = $this->surveySettingsData->field_desc4_duplicate_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_link_default_else) && count($this->surveySettingsData->field_link_default_else->getValue())) {
        $this->field_link_default_else = $this->surveySettingsData->field_link_default_else->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_link_qualitytermi_grt_0) && count($this->surveySettingsData->field_link_qualitytermi_grt_0->getValue())) {
        $this->field_link_qualitytermi_grt_0 = $this->surveySettingsData->field_link_qualitytermi_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_link_suspicious) && count($this->surveySettingsData->field_link_suspicious->getValue())) {
        $this->field_link_suspicious = $this->surveySettingsData->field_link_suspicious->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_link_complete) && count($this->surveySettingsData->field_link_complete->getValue())) {
        $this->field_link_complete = $this->surveySettingsData->field_link_complete->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_badgeoip) && count($this->surveySettingsData->field_title_badgeoip->getValue())) {
        $this->field_title_badgeoip = $this->surveySettingsData->field_title_badgeoip->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_badtoken_0) && count($this->surveySettingsData->field_title_badtoken_0->getValue())) {
        $this->field_title_badtoken_0 = $this->surveySettingsData->field_title_badtoken_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_badtoken_grt_0) && count($this->surveySettingsData->field_title_badtoken_grt_0->getValue())) {
        $this->field_title_badtoken_grt_0 = $this->surveySettingsData->field_title_badtoken_grt_0->getValue()[0]['value'];
      }

      if (isset($this->surveySettingsData->field_title_earlyscrout_0) && count($this->surveySettingsData->field_title_earlyscrout_0->getValue())) {
        $this->field_title_earlyscrout_0 = $this->surveySettingsData->field_title_earlyscrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_earlyscrout_grt_0) && count($this->surveySettingsData->field_title_earlyscrout_grt_0->getValue())) {
        $this->field_title_earlyscrout_grt_0 = $this->surveySettingsData->field_title_earlyscrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_fraud) && count($this->surveySettingsData->field_title_fraud->getValue())) {
        $this->field_title_fraud = $this->surveySettingsData->field_title_fraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_duplicate) && count($this->surveySettingsData->field_title_duplicate->getValue())) {
        $this->field_title_duplicate = $this->surveySettingsData->field_title_duplicate->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_prescr_term) && count($this->surveySettingsData->field_title_prescr_term->getValue())) {
        $this->field_title_prescr_term = $this->surveySettingsData->field_title_prescr_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_qualityterminate) && count($this->surveySettingsData->field_title_qualityterminate->getValue())) {
        $this->field_title_qualityterminate = $this->surveySettingsData->field_title_qualityterminate->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_quotafull) && count($this->surveySettingsData->field_title_quotafull->getValue())) {
        $this->field_title_quotafull = $this->surveySettingsData->field_title_quotafull->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_relvidfail) && count($this->surveySettingsData->field_title_relvidfail->getValue())) {
        $this->field_title_relvidfail = $this->surveySettingsData->field_title_relvidfail->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_relvidfraud) && count($this->surveySettingsData->field_title_relvidfraud->getValue())) {
        $this->field_title_relvidfraud = $this->surveySettingsData->field_title_relvidfraud->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_surveyclosed) && count($this->surveySettingsData->field_title_surveyclosed->getValue())) {
        $this->field_title_surveyclosed = $this->surveySettingsData->field_title_surveyclosed->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_suspicious) && count($this->surveySettingsData->field_title_suspicious->getValue())) {
        $this->field_title_suspicious = $this->surveySettingsData->field_title_suspicious->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_target_scrout_0) && count($this->surveySettingsData->field_title_target_scrout_0->getValue())) {
        $this->field_title_target_scrout_0 = $this->surveySettingsData->field_title_target_scrout_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_target_scrout_grt_0) && count($this->surveySettingsData->field_title_target_scrout_grt_0->getValue())) {
        $this->field_title_target_scrout_grt_0 = $this->surveySettingsData->field_title_target_scrout_grt_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_tech_term) && count($this->surveySettingsData->field_title_tech_term->getValue())) {
        $this->field_title_tech_term = $this->surveySettingsData->field_title_tech_term->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_complete_0) && count($this->surveySettingsData->field_title_complete_0->getValue())) {
        $this->field_title_complete_0 = $this->surveySettingsData->field_title_complete_0->getValue()[0]['value'];
      }
      if (isset($this->surveySettingsData->field_title_complete_grt_0) && count($this->surveySettingsData->field_title_complete_grt_0->getValue())) {
        $this->field_title_complete_grt_0 = $this->surveySettingsData->field_title_complete_grt_0->getValue()[0]['value'];
      }
    }
  }

  /**
   * Getter for all survey configurations.
   */
  public function getSurveysConfiguration() {
    return [
      'field_title_default_else' => $this->field_title_default_else,
      'field_desc1_badgeoip' => $this->field_desc1_badgeoip,
      'field_desc1_badtoken_0' => $this->field_desc1_badtoken_0,
      'field_desc1_badtoken_grt_0' => $this->field_desc1_badtoken_grt_0,
      'field_desc1_earlyscrout_0' => $this->field_desc1_earlyscrout_0,
      'field_desc1_earlyscrout_grt_0' => $this->field_desc1_earlyscrout_grt_0,
      'field_desc1_fraud' => $this->field_desc1_fraud,
      'field_desc1_duplicate' => $this->field_desc1_duplicate,
      'field_desc1_prescr_term' => $this->field_desc1_prescr_term,
      'field_desc1_qualitytermi_grt_0' => $this->field_desc1_qualitytermi_grt_0,
      'field_desc1_quotafull' => $this->field_desc1_quotafull,
      'field_desc1_relvidfail' => $this->field_desc1_relvidfail,
      'field_desc1_relvidfraud' => $this->field_desc1_relvidfraud,
      'field_desc1_surveyclosed' => $this->field_desc1_surveyclosed,
      'field_desc1_target_scrout_0' => $this->field_desc1_target_scrout_0,
      'field_desc1_target_scrout_grt_0' => $this->field_desc1_target_scrout_grt_0,
      'field_desc1_tech_term' => $this->field_desc1_tech_term,
      'field_desc1_complete_grt_0' => $this->field_desc1_complete_grt_0,
      'field_desc2_complete_grt_0' => $this->field_desc2_complete_grt_0,
      'field_desc2_badtoken_0' => $this->field_desc2_badtoken_0,
      'field_desc2_badtoken_grt_0' => $this->field_desc2_badtoken_grt_0,
      'field_desc2_earlyscrout_0' => $this->field_desc2_earlyscrout_0,
      'field_desc2_earlyscrout_grt_0' => $this->field_desc2_earlyscrout_grt_0,
      'field_desc2_fraud' => $this->field_desc2_fraud,
      'field_desc2_duplicate' => $this->field_desc2_duplicate,
      'field_desc2_prescr_term' => $this->field_desc2_prescr_term,
      'field_desc2_quotafull' => $this->field_desc2_quotafull,
      'field_desc2_relvidfraud' => $this->field_desc2_relvidfraud,
      'field_desc2_surveyclosed' => $this->field_desc2_surveyclosed,
      'field_desc2_target_scrout_0' => $this->field_desc2_target_scrout_0,
      'field_desc2_target_scrout_grt_0' => $this->field_desc2_target_scrout_grt_0,
      'field_desc2_tech_term' => $this->field_desc2_tech_term,
      'field_desc3_duplicate_0' => $this->field_desc3_duplicate_0,
      'field_desc3_tech_term' => $this->field_desc3_tech_term,
      'field_desc4_duplicate_0' => $this->field_desc4_duplicate_0,
      'field_link_default_else' => $this->field_link_default_else,
      'field_link_qualitytermi_grt_0' => $this->field_link_qualitytermi_grt_0,
      'field_link_suspicious' => $this->field_link_suspicious,
      'field_link_complete' => $this->field_link_complete,
      'field_title_badgeoip' => $this->field_title_badgeoip,
      'field_title_badtoken_0' => $this->field_title_badtoken_0,
      'field_title_badtoken_grt_0' => $this->field_title_badtoken_grt_0,
      'field_title_earlyscrout_0' => $this->field_title_earlyscrout_0,
      'field_title_earlyscrout_grt_0' => $this->field_title_earlyscrout_grt_0,
      'field_title_fraud' => $this->field_title_fraud,
      'field_title_duplicate' => $this->field_title_duplicate,
      'field_title_prescr_term' => $this->field_title_prescr_term,
      'field_title_qualityterminate' => $this->field_title_qualityterminate,
      'field_title_quotafull' => $this->field_title_quotafull,
      'field_title_relvidfail' => $this->field_title_relvidfail,
      'field_title_relvidfraud' => $this->field_title_relvidfraud,
      'field_title_surveyclosed' => $this->field_title_surveyclosed,
      'field_title_suspicious' => $this->field_title_suspicious,
      'field_title_target_scrout_0' => $this->field_title_target_scrout_0,
      'field_title_target_scrout_grt_0' => $this->field_title_target_scrout_grt_0,
      'field_title_tech_term' => $this->field_title_tech_term,
      'field_title_complete_0' => $this->field_title_complete_0,
      'field_title_complete_grt_0' => $this->field_title_complete_grt_0,
    ];
  }

}
