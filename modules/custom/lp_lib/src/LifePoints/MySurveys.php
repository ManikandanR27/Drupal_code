<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;
use Drupal\Core\Url;

/**
 * WS response object for common service for My Surveys.
 */
class MySurveys {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while retrieving studies';

  protected $wsClient;
  protected $userSession;

  /**
   * Construct for getting surveys to login panelist.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }

  /**
   * Gets all device properties from a given agent string.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getAllDeviceProperties(array $data) {
    $response = $this->wsClient->GET('OneP-AccountServices/AllProperties', $data);
    return $response;
  }

  /**
   * Survey Portal GET request.
   *
   * Combined services Panelist Get Surveys to return first profiler study if the panelist didn't finish them, otherwise all prioritized studies.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getSurveyProfilerPrioritized(array $data, array $panelistData) {
    $response = $this->wsClient->surveyPortalGet('OneP-Survey/Survey/PanelistId', $data, $panelistData);
    return $response;
  }

  /**
   * Survey Portal PUT Endpoint to confirm Panelist has completed his/her Survey.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getSurveyProfilerActivity(array $data, array $panelistData) {
    $response = $this->wsClient->surveyPortalPut('OneP-Survey/ProfilerActivity/PanelistId', $data, $panelistData);
    return $response;
  }

  /**
   * Survey Portal GET Endpoint to Verify Survey Finish has completed his/her Survey.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getSurveyVerifyRespondentFinish(array $data, array $panelistData) {
    if (isset($panelistData['respondentGuid'])) {
      $respondentGuid = $panelistData['respondentGuid'];
      unset($panelistData['respondentGuid']);
    }
    $response = $this->wsClient->surveyPortalGet('OneP-Survey/Survey/Verify/Respondent/' . $respondentGuid . '/Status', $data, $panelistData);
    return $response;
  }

  /**
   * Method to verify the survey start.
   * If allowed, panelist should redirect to survey page.
   * If abort, panelist should redirect back to dashboard page.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function surveyStartCheck($jwt, $panelistData) {
    $response = $this->wsClient->GET('OneP-Survey/Survey/Start', $panelistData, [$jwt]);
    return $response;
  }

  /**
   * Method to get panelist Statistics.
   * 
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getSurveyStatistics(array $data, array $panelistData) {
    $response = $this->wsClient->GET('OneP-Survey/Statistics/PanelistId',$data,$panelistData);
    return $response;
  }
}
