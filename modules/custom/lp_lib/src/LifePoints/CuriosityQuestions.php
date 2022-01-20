<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class CuriosityQuestions {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred, please try after sometime.';
  protected $wsClient;
  protected $userSession;

  /**
   * Cunstructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }

  /**
   * Call get question endpoint.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getQuestions(array $data) {
    if ((!isset($data["country"])) && (!isset($data["language"])) && (!isset($data["domain"])) && (!isset($data["panelist_id"]))) {
      throw new \Exception(self::DEFAULT_ERROR_MSG);
    }
    if (isset($data["max_questions"])) {
      $max_questions = $data["max_questions"];
      unset($data['max_questions']);
    }
    else {
      // Get the curiosity question module configuration.
      $config = \Drupal::config('lp_curiosity_question.settings');
      $max_questions = $config->get('max_questions');
    }

    $country = $data["country"];
    $language = $data["language"];
    $domain = $data["domain"];
    $panelist_id = $data["panelist_id"];
    // Unset data variables.
    unset($data['country']);
    unset($data['language']);
    unset($data['domain']);
    unset($data['panelist_id']);
    $api_endpoint = 'next-questions/' . $country . '/' . $language . '/' . $domain . '/' . $panelist_id . '/' . $max_questions;
    // Endpoint to get question ids.
    $data = [];
    $response = $this->wsClient->get($api_endpoint,
      $data,
      ['urlParams' => NULL]);
    return $response;
  }

  /**
   * Call get next question endpoint.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function getNextQuestions($question_id) {
    if (!isset($question_id)) {
      throw new \Exception(self::DEFAULT_ERROR_MSG);
    }
    $api_endpoint = 'questions/' . $question_id;
    // Endpoint to get question details based on id's.
    $data = [];
    $response = $this->wsClient->get($api_endpoint, $data);
    return $response;
  }

  /**
   * Call submit answer endpoint.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function submitAnswer(array $data) {
    if (!isset($data) && empty($data)) {
      throw new \Exception(self::DEFAULT_ERROR_MSG);
    }
    // Get the curiosity question module configuration.
    $question_id = $data["question_id"];
    unset($data["question_id"]);
    $api_endpoint = 'answer/' . $question_id;
    // Endpoint to submit answer based on question id.
    $response = $this->wsClient->putJson($api_endpoint, $data,
      ['urlParams' => NULL]
    );
    return $response;
  }

}
